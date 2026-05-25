<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function timeRangeLabel(): string
    {
        return substr($this->start_time, 0, 5) . ' – ' . substr($this->end_time, 0, 5);
    }

    public static function activeAt(?Carbon $at = null): ?self
    {
        $at    = $at ?? now();
        $time  = $at->format('H:i:s');

        return static::query()
            ->where('is_active', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->orderBy('sort_order')
            ->first();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, self> */
    public static function endedForDate(Carbon $date, ?Carbon $asOf = null)
    {
        $asOf = $asOf ?? now();

        if ($date->toDateString() !== $asOf->toDateString()) {
            return $date->isPast()
                ? static::query()->where('is_active', true)->orderBy('sort_order')->get()
                : collect();
        }

        $time = $asOf->format('H:i:s');

        return static::query()
            ->where('is_active', true)
            ->where('end_time', '<=', $time)
            ->orderBy('sort_order')
            ->get();
    }

    public function endDateTimeFor(Carbon $date): Carbon
    {
        return Carbon::parse($date->toDateString() . ' ' . $this->end_time);
    }

    /** Time of day (H:i) when the absent-mark job should run after this period ends. */
    public function absentMarkScheduleTime(int $bufferMinutes = 2): string
    {
        return $this->parseTime($this->end_time)
            ->addMinutes($bufferMinutes)
            ->format('H:i');
    }

    public static function catchAllScheduleTime(int $bufferMinutes = 30): ?string
    {
        $lastEnd = static::query()->where('is_active', true)->max('end_time');

        if (!$lastEnd) {
            return null;
        }

        return static::parseTime($lastEnd)->addMinutes($bufferMinutes)->format('H:i');
    }

    private static function parseTime(string $time): Carbon
    {
        $normalized = strlen($time) === 5 ? $time . ':00' : $time;

        return Carbon::createFromFormat('H:i:s', $normalized);
    }
}

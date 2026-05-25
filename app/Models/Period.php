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
}

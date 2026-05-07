<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Console\Command;

class MarkAbsentStudents extends Command
{
    protected $signature   = 'attendance:mark-absent {--date= : Date to mark absent for (Y-m-d), defaults to today}';
    protected $description = 'Mark every student with no attendance record for the day as absent';

    public function handle(): int
    {
        $date = $this->option('date')
            ? now()->parse($this->option('date'))->toDateString()
            : now()->toDateString();

        $this->info("Marking absent for {$date}...");

        $recorded = AttendanceRecord::whereDate('marked_at', $date)
            ->pluck('student_id')
            ->all();

        $unrecorded = Student::with('schoolClass')
            ->whereNotIn('id', $recorded)
            ->get();

        if ($unrecorded->isEmpty()) {
            $this->info('All students already have a record for today.');
            return self::SUCCESS;
        }

        $now    = now();
        $rows   = $unrecorded->map(fn($s) => [
            'student_id' => $s->id,
            'class_id'   => $s->class_id,
            'status'     => 'absent',
            'method'     => 'auto',
            'marked_at'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        AttendanceRecord::insert($rows);

        $this->info("Marked {$unrecorded->count()} student(s) absent.");

        return self::SUCCESS;
    }
}

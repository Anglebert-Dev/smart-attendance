<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Period;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentStudents extends Command
{
    protected $signature   = 'attendance:mark-absent {--date= : Date to mark absent for (Y-m-d), defaults to today}';
    protected $description = 'Mark students absent for each ended period they have no record for';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $dateString = $date->toDateString();
        $periods      = Period::endedForDate($date, now());

        if ($periods->isEmpty()) {
            $this->info("No ended periods to process for {$dateString}.");
            return self::SUCCESS;
        }

        $this->info("Marking period absences for {$dateString}...");
        $total = 0;

        foreach ($periods as $period) {
            $markedAt = $period->endDateTimeFor($date);

            $students = Student::query()->get();

            foreach ($students as $student) {
                $exists = AttendanceRecord::query()
                    ->where('student_id', $student->id)
                    ->where('class_id', $student->class_id)
                    ->where('period_id', $period->id)
                    ->whereDate('marked_at', $dateString)
                    ->exists();

                if ($exists) {
                    continue;
                }

                AttendanceRecord::create([
                    'student_id' => $student->id,
                    'class_id'   => $student->class_id,
                    'period_id'  => $period->id,
                    'status'     => 'absent',
                    'method'     => 'auto',
                    'marked_at'  => $markedAt,
                ]);

                $total++;
            }

            $this->line("  {$period->name}: absences filled.");
        }

        $this->info("Marked {$total} absent record(s) across ended periods.");
        \Illuminate\Support\Facades\Log::info("MarkAbsentStudents: {$total} absent record(s) for {$dateString}.");

        return self::SUCCESS;
    }
}

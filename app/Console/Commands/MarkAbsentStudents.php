<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Period;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentStudents extends Command
{
    protected $signature   = 'attendance:mark-absent
                            {--date= : Date to mark absent for (Y-m-d), defaults to today}
                            {--period= : Only process this period ID}';

    protected $description = 'Mark students absent for each ended period they have no record for';

    public function handle(): int
    {
        $date       = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateString = $date->toDateString();
        $periods    = $this->resolvePeriods($date);

        if ($periods->isEmpty()) {
            $this->info("No periods to process for {$dateString}.");
            return self::SUCCESS;
        }

        $this->info("Marking period absences for {$dateString}...");
        $total = 0;

        foreach ($periods as $period) {
            $markedAt = $period->endDateTimeFor($date);
            $created  = 0;

            foreach (Student::query()->get() as $student) {
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

                $created++;
                $total++;
            }

            $this->line("  {$period->name}: {$created} absent record(s).");
        }

        $this->info("Marked {$total} absent record(s) across {$periods->count()} period(s).");
        \Illuminate\Support\Facades\Log::info("MarkAbsentStudents: {$total} absent record(s) for {$dateString}.");

        return self::SUCCESS;
    }

    /** @return \Illuminate\Support\Collection<int, Period> */
    private function resolvePeriods(Carbon $date)
    {
        if ($periodId = $this->option('period')) {
            $period = Period::query()->where('is_active', true)->find($periodId);

            if (!$period) {
                $this->error("Period #{$periodId} not found or inactive.");
                return collect();
            }

            if ($date->isToday() && now()->format('H:i:s') < $period->end_time) {
                $this->info("{$period->name} has not ended yet — skipping.");
                return collect();
            }

            return collect([$period]);
        }

        return Period::endedForDate($date, $date->isToday() ? now() : $date->copy()->endOfDay());
    }
}

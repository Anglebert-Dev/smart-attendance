<?php

namespace App\Services;

use App\Models\Period;
use Carbon\Carbon;

class AttendancePeriodService
{
    public function resolveActivePeriod(?Carbon $at = null): ?Period
    {
        return Period::activeAt($at);
    }

    public function hasRecordFor(int $studentId, int $classId, int $periodId, Carbon $date): bool
    {
        return \App\Models\AttendanceRecord::query()
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('period_id', $periodId)
            ->whereDate('marked_at', $date->toDateString())
            ->exists();
    }
}

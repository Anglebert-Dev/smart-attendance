<?php

namespace App\Services;

use App\Models\AttendanceDetection;
use App\Models\Period;
use Carbon\Carbon;

class AttendancePeriodService
{
    const DETECTION_WINDOW_MINUTES   = 10;
    const REQUIRED_DETECTIONS        = 3;
    const DETECTION_INTERVAL_MINUTES = 3;

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

    public function isWithinDetectionWindow(Period $period, Carbon $at): bool
    {
        $windowEnd = Carbon::parse($at->toDateString() . ' ' . $period->start_time)
            ->addMinutes(self::DETECTION_WINDOW_MINUTES);

        return $at->lessThanOrEqualTo($windowEnd);
    }

    public function isDetectionIntervalMet(int $studentId, int $classId, int $periodId, Carbon $date, Carbon $at): bool
    {
        $last = AttendanceDetection::query()
            ->where('student_id', $studentId)
            ->where('class_id',   $classId)
            ->where('period_id',  $periodId)
            ->whereDate('detected_at', $date->toDateString())
            ->latest('detected_at')
            ->value('detected_at');

        if (!$last) {
            return true;
        }

        return Carbon::parse($last)->diffInMinutes($at) >= self::DETECTION_INTERVAL_MINUTES;
    }

    public function logDetection(int $studentId, int $classId, int $periodId, Carbon $at): void
    {
        AttendanceDetection::create([
            'student_id'  => $studentId,
            'class_id'    => $classId,
            'period_id'   => $periodId,
            'detected_at' => $at,
        ]);
    }

    public function countDetections(int $studentId, int $classId, int $periodId, Carbon $date): int
    {
        return AttendanceDetection::query()
            ->where('student_id', $studentId)
            ->where('class_id',   $classId)
            ->where('period_id',  $periodId)
            ->whereDate('detected_at', $date->toDateString())
            ->count();
    }
}

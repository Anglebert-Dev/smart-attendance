<?php

namespace App\Traits;

use App\Models\AttendanceRecord;
use App\Models\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersAttendanceRecords
{
    protected function filteredAttendanceQuery(Request $request, ?array $classIds = null): Builder
    {
        return AttendanceRecord::with(['student', 'schoolClass', 'period'])
            ->when($classIds, fn (Builder $q) => $q->whereIn('class_id', $classIds))
            ->when($request->search, fn (Builder $q) =>
                $q->whereHas('student', fn (Builder $sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->when($request->class_id, fn (Builder $q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn (Builder $q) => $q->whereDate('marked_at', $request->date))
            ->when($request->period_id, fn (Builder $q) => $q->where('period_id', $request->period_id))
            ->latest('marked_at');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Period> */
    protected function periodsForFilter()
    {
        return Period::query()->where('is_active', true)->orderBy('sort_order')->get();
    }
}

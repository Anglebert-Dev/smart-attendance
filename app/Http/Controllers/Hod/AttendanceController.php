<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\AttendanceRecord;
use App\Traits\ExportsAttendanceCsv;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ExportsAttendanceCsv;
    use ScopesToDepartment;

    public function index(Request $request)
    {
        $department = $this->hodDepartmentCode();
        $classIds   = $this->departmentClassIds();
        $classes    = $this->departmentClassesQuery()->orderBy('name')->get();

        if ($request->class_id && !in_array((int) $request->class_id, $classIds, true)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->when($request->search, fn ($q) =>
                $q->whereHas('student', fn ($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn ($q) => $q->whereDate('marked_at', $request->date))
            ->latest('marked_at')
            ->paginate(20)
            ->withQueryString();

        return view('hod.attendance.index', compact('records', 'classes', 'department'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->loadMissing('schoolClass');
        $this->ensureClassInDepartment($record->schoolClass);

        $record->load(['student.schoolClass', 'schoolClass.teachers']);

        return view('hod.attendance.show', compact('record'));
    }

    public function exportCsv(Request $request)
    {
        $classIds = $this->departmentClassIds();

        if ($request->class_id && !in_array((int) $request->class_id, $classIds, true)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->when($request->search, fn ($q) =>
                $q->whereHas('student', fn ($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn ($q) => $q->whereDate('marked_at', $request->date))
            ->latest('marked_at')
            ->get();

        return $this->downloadCsv($records);
    }
}

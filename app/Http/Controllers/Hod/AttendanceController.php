<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\AttendanceDetection;
use App\Models\AttendanceRecord;
use App\Traits\ExportsAttendanceCsv;
use App\Traits\FiltersAttendanceRecords;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ExportsAttendanceCsv;
    use FiltersAttendanceRecords;
    use ScopesToDepartment;

    public function index(Request $request)
    {
        $department = $this->hodDepartmentCode();
        $classIds   = $this->departmentClassIds();
        $classes    = $this->departmentClassesQuery()->orderBy('name')->get();
        $periods    = $this->periodsForFilter();

        if ($request->class_id && !in_array((int) $request->class_id, $classIds, true)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = $this->filteredAttendanceQuery($request, $classIds)
            ->paginate(20)
            ->withQueryString();

        return view('hod.attendance.index', compact('records', 'classes', 'periods', 'department'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->loadMissing('schoolClass');
        $this->ensureClassInDepartment($record->schoolClass);

        $record->load(['student.schoolClass', 'schoolClass.teachers', 'period']);

        $detections = AttendanceDetection::query()
            ->where('student_id', $record->student_id)
            ->where('class_id', $record->class_id)
            ->where('period_id', $record->period_id)
            ->whereDate('detected_at', $record->marked_at->toDateString())
            ->orderBy('detected_at')
            ->get();

        return view('hod.attendance.show', compact('record', 'detections'));
    }

    public function exportCsv(Request $request)
    {
        $classIds = $this->departmentClassIds();

        if ($request->class_id && !in_array((int) $request->class_id, $classIds, true)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = $this->filteredAttendanceQuery($request, $classIds)->get();

        return $this->downloadCsv($records);
    }
}

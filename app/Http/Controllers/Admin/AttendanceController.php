<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetection;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Traits\ExportsAttendanceCsv;
use App\Traits\FiltersAttendanceRecords;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ExportsAttendanceCsv;
    use FiltersAttendanceRecords;

    public function index(Request $request)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $periods  = $this->periodsForFilter();
        $records  = $this->filteredAttendanceQuery($request)
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.index', compact('records', 'classes', 'periods'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->load(['student.schoolClass', 'schoolClass.teachers', 'period']);

        $detections = AttendanceDetection::query()
            ->where('student_id', $record->student_id)
            ->where('class_id', $record->class_id)
            ->where('period_id', $record->period_id)
            ->whereDate('detected_at', $record->marked_at->toDateString())
            ->orderBy('detected_at')
            ->get();

        return view('admin.attendance.show', compact('record', 'detections'));
    }

    public function exportCsv(Request $request)
    {
        $records = $this->filteredAttendanceQuery($request)->get();

        return $this->downloadCsv($records);
    }
}

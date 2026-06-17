<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetection;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Traits\ExportsAttendanceCsv;
use App\Traits\FiltersAttendanceRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    use ExportsAttendanceCsv;
    use FiltersAttendanceRecords;

    public function index(Request $request)
    {
        $classIds = SchoolClass::forTeacher(Auth::id())->pluck('id');
        $classes  = SchoolClass::forTeacher(Auth::id())->get();
        $periods  = $this->periodsForFilter();

        if ($request->class_id && !$classIds->contains((int) $request->class_id)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = $this->filteredAttendanceQuery($request, $classIds->all())
            ->paginate(20)
            ->withQueryString();

        return view('teacher.attendance', compact('records', 'classes', 'periods'));
    }

    public function show(AttendanceRecord $record)
    {
        if (!$record->schoolClass->hasTeacher(Auth::id())) {
            abort(403, 'You do not have access to this record.');
        }

        $record->load(['student.schoolClass', 'schoolClass.teachers', 'period']);

        $detections = AttendanceDetection::query()
            ->where('student_id', $record->student_id)
            ->where('class_id', $record->class_id)
            ->where('period_id', $record->period_id)
            ->whereDate('detected_at', $record->marked_at->toDateString())
            ->orderBy('detected_at')
            ->get();

        return view('teacher.attendance.show', compact('record', 'detections'));
    }

    public function exportCsv(Request $request)
    {
        $classIds = SchoolClass::forTeacher(Auth::id())->pluck('id');

        if ($request->class_id && !$classIds->contains((int) $request->class_id)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = $this->filteredAttendanceQuery($request, $classIds->all())->get();

        return $this->downloadCsv($records);
    }
}

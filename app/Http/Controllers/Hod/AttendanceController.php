<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();

        $records = AttendanceRecord::with(['student', 'schoolClass'])
            ->when($request->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn($q) => $q->whereDate('marked_at', $request->date))
            ->latest('marked_at')
            ->paginate(20)
            ->withQueryString();

        return view('hod.attendance.index', compact('records', 'classes'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->load(['student.schoolClass', 'schoolClass.teacher']);
        return view('hod.attendance.show', compact('record'));
    }
}

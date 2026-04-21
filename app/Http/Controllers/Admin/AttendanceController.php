<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.attendance.index', compact('records', 'classes'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->load(['student.schoolClass', 'schoolClass.teacher']);
        return view('admin.attendance.show', compact('record'));
    }
}

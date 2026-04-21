<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $records = AttendanceRecord::with('student')->latest()->paginate(50);
        return view('hod.attendance.index', compact('records'));
    }

    public function show(AttendanceRecord $record)
    {
        $record->load(['student.schoolClass', 'schoolClass.teacher']);
        return view('hod.attendance.show', compact('record'));
    }
}

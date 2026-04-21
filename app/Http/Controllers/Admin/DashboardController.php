<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'classes'          => SchoolClass::count(),
            'students'         => Student::count(),
            'teachers'         => User::where('role', 'teacher')->count(),
            'hods'             => User::where('role', 'hod')->count(),
            'today_attendance' => AttendanceRecord::whereDate('marked_at', today())->count(),
        ];

        $recentAttendance = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereDate('marked_at', today())
            ->latest('marked_at')
            ->take(8)
            ->get();

        $classes = SchoolClass::with('teacher')
            ->withCount('students')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendance', 'classes'));
    }
}

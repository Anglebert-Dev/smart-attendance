<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher  = Auth::user();
        $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');

        $stats = [
            'classes'          => $classIds->count(),
            'students'         => Student::whereIn('class_id', $classIds)->count(),
            'today_attendance' => AttendanceRecord::whereIn('class_id', $classIds)
                ->whereDate('marked_at', today())->count(),
        ];

        $classes = SchoolClass::where('teacher_id', $teacher->id)
            ->withCount('students')
            ->get();

        $recentAttendance = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->whereDate('marked_at', today())
            ->latest('marked_at')
            ->take(8)
            ->get();

        return view('teacher.dashboard', compact('stats', 'classes', 'recentAttendance'));
    }
}

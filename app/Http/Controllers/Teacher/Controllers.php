<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();

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

// -------------------------------------------------------

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::where('teacher_id', Auth::id())
            ->withCount('students')
            ->get();

        return view('teacher.classes', compact('classes'));
    }
}

// -------------------------------------------------------

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $classIds = SchoolClass::where('teacher_id', Auth::id())->pluck('id');
        $classes  = SchoolClass::where('teacher_id', Auth::id())->get();

        $students = Student::with(['schoolClass', 'attendanceToday'])
            ->whereIn('class_id', $classIds)
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
            )
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('teacher.students', compact('students', 'classes'));
    }
}

// -------------------------------------------------------

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classIds = SchoolClass::where('teacher_id', Auth::id())->pluck('id');
        $classes  = SchoolClass::where('teacher_id', Auth::id())->get();

        $records = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn($q) => $q->whereDate('marked_at', $request->date))
            ->latest('marked_at')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.attendance', compact('records', 'classes'));
    }
}

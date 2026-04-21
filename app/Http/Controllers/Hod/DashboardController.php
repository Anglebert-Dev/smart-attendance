<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_teachers'   => User::where('role', 'teacher')->count(),
            'total_classes'    => SchoolClass::count(),
            'total_students'   => Student::count(),
            'today_attendance' => AttendanceRecord::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('hod.dashboard', compact('stats'));
    }
}

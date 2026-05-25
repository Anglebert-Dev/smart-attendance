<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    use ScopesToDepartment;

    public function index()
    {
        $department = $this->hodDepartmentCode();
        $classIds   = $this->departmentClassIds();

        $stats = [
            'department'       => $department,
            'total_teachers'   => User::where('role', 'teacher')
                ->whereHas('classes', fn ($q) => $q->whereIn('school_classes.id', $classIds))
                ->count(),
            'total_classes'    => count($classIds),
            'total_students'   => Student::whereIn('class_id', $classIds)->count(),
            'today_attendance' => AttendanceRecord::whereIn('class_id', $classIds)
                ->whereDate('marked_at', today())
                ->count(),
        ];

        return view('hod.dashboard', compact('stats'));
    }
}

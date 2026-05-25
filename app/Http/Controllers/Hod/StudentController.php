<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\Student;

class StudentController extends Controller
{
    use ScopesToDepartment;

    public function index()
    {
        $department = $this->hodDepartmentCode();
        $classIds   = $this->departmentClassIds();

        $students = Student::with('schoolClass')
            ->whereIn('class_id', $classIds)
            ->orderBy('name')
            ->get();

        return view('hod.students.index', compact('students', 'department'));
    }
}

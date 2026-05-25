<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\User;

class TeacherController extends Controller
{
    use ScopesToDepartment;

    public function index()
    {
        $department = $this->hodDepartmentCode();
        $classIds   = $this->departmentClassIds();

        $teachers = User::where('role', 'teacher')
            ->whereHas('classes', fn ($q) => $q->whereIn('school_classes.id', $classIds))
            ->with(['classes' => fn ($q) => $q->whereIn('school_classes.id', $classIds)->orderBy('name')])
            ->withCount(['classes' => fn ($q) => $q->whereIn('school_classes.id', $classIds)])
            ->orderBy('name')
            ->get();

        return view('hod.teachers.index', compact('teachers', 'department'));
    }
}

<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hod\Concerns\ScopesToDepartment;
use App\Models\SchoolClass;

class ClassController extends Controller
{
    use ScopesToDepartment;

    public function index()
    {
        $department = $this->hodDepartmentCode();
        $classes    = $this->departmentClassesQuery()
            ->with('teachers')
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return view('hod.classes.index', compact('classes', 'department'));
    }

    public function show(SchoolClass $class)
    {
        $this->ensureClassInDepartment($class);
        $class->load(['teachers', 'students']);

        return view('hod.classes.show', compact('class'));
    }
}

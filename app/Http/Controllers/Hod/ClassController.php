<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('teacher')->withCount('students')->get();
        return view('hod.classes.index', compact('classes'));
    }

    public function show(SchoolClass $class)
    {
        $class->load(['teacher', 'students']);
        return view('hod.classes.show', compact('class'));
    }
}

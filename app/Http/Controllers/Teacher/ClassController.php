<?php

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

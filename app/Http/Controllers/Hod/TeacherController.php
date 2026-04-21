<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'teacher')->withCount('classes')->get();
        return view('hod.teachers.index', compact('teachers'));
    }
}

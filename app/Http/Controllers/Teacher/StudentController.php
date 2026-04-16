<?php

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

        if ($request->class_id && !$classIds->contains((int) $request->class_id)) {
            abort(403, 'You do not have access to this class.');
        }

        if ($request->class_id && !$classIds->contains((int) $request->class_id)) {
            abort(403, 'You do not have access to this class.');
        }

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

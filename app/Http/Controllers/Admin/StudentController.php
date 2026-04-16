<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();

        $students = Student::with('schoolClass')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('student_id', 'like', "%{$request->search}%")
            )
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.form', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'student_id' => 'required|string|unique:students,student_id',
            'email'      => 'nullable|email|unique:students,email',
            'class_id'   => 'required|exists:school_classes,id',
            'photos'     => 'nullable|array|max:10',
            'photos.*'   => 'image|max:5120',
        ]);

        unset($data['photos']);
        $student = Student::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                $path = $file->store('students', 'public');
                $student->photos()->create(['path' => $path]);
                if ($index === 0) {
                    $student->update(['photo' => $path]);
                }
            }
            $student->update(['face_encoded' => false]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student added successfully.');
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $student->load('photos');
        return view('admin.students.form', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'student_id' => "required|string|unique:students,student_id,{$student->id}",
            'email'      => "nullable|email|unique:students,email,{$student->id}",
            'class_id'   => 'required|exists:school_classes,id',
            'photos'     => 'nullable|array|max:10',
            'photos.*'   => 'image|max:5120',
        ]);

        unset($data['photos']);

        if ($student->class_id != $data['class_id']) {
            $data['face_encoded'] = false;
        }

        $student->update($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('students', 'public');
                $student->photos()->create(['path' => $path]);
            }
            // Update avatar to first photo if none set yet
            if (!$student->photo) {
                $student->update(['photo' => $student->photos()->first()->path]);
            }
            $student->update(['face_encoded' => false]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroyPhoto(Student $student, StudentPhoto $photo)
    {
        abort_if($photo->student_id !== $student->id, 403);

        $photo->delete(); // Storage::delete handled by model boot

        // If deleted photo was the avatar, reassign to next available
        if ($student->photo === $photo->path) {
            $next = $student->photos()->first();
            $student->update(['photo' => $next?->path ?? null]);
        }

        return back()->with('success', 'Photo removed.');
    }

    public function destroy(Student $student)
    {
        // Deletes all StudentPhoto records (and their files) via model boot
        $student->photos()->each(fn($p) => $p->delete());

        if ($student->photo) Storage::disk('public')->delete($student->photo);
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted.');
    }
}

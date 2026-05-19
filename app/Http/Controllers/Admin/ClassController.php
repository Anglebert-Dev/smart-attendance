<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('teachers')
            ->withCount('students')
            ->latest()
            ->paginate(15);

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.form', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
            'teacher_ids'   => 'nullable|array',
            'teacher_ids.*' => Rule::exists('users', 'id')->where('role', 'teacher'),
        ]);

        $class = SchoolClass::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $class->teachers()->sync($data['teacher_ids'] ?? []);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    public function edit(SchoolClass $class)
    {
        $class->load('teachers');
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.form', compact('class', 'teachers'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
            'teacher_ids'   => 'nullable|array',
            'teacher_ids.*' => Rule::exists('users', 'id')->where('role', 'teacher'),
        ]);

        $class->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $class->teachers()->sync($data['teacher_ids'] ?? []);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted.');
    }
}

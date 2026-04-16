@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Students')
@section('page-subtitle', 'Manage all enrolled students')

@section('header-actions')
    <a href="{{ route('admin.students.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Student
    </a>
@endsection

@section('content')
{{-- Filters --}}
<div class="card mb-5">
    <form method="GET">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                class="input sm:flex-1" style="min-width:0;" placeholder="Search by name or student ID...">
            <select name="class_id" class="input sm:w-auto">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                @if(request()->hasAny(['search','class_id']))
                    <a href="{{ route('admin.students.index') }}" class="btn-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if($students->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:620px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student ID</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Face Data</th>
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($students as $student)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-100" style="flex-shrink:0;">
                            @else
                                <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center text-green-700 text-sm font-bold" style="flex-shrink:0;">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-slate-900 text-sm whitespace-nowrap">{{ $student->name }}</p>
                                <p class="text-xs text-slate-400">{{ $student->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5">
                        <span class="badge-slate font-mono whitespace-nowrap">{{ $student->student_id }}</span>
                    </td>
                    <td class="py-3.5">
                        @if($student->schoolClass)
                            <span class="badge-blue whitespace-nowrap">{{ $student->schoolClass->name }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">No class</span>
                        @endif
                    </td>
                    <td class="py-3.5">
                        @if($student->face_encoded)
                            <span class="badge-green">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Encoded
                            </span>
                        @else
                            <span class="badge-slate">Not encoded</span>
                        @endif
                    </td>
                    <td class="py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Delete this student?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger py-1.5 px-3 text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mt-4">{{ $students->links() }}</div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>
            <p class="font-medium text-slate-600 mb-1">No students found</p>
            <p class="text-sm mb-4">Add your first student to get started</p>
            <a href="{{ route('admin.students.create') }}" class="btn-primary">Add Student</a>
        </div>
    @endif
</div>
@endsection

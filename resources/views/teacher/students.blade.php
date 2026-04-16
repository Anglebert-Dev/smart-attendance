@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Students')
@section('page-subtitle', 'Students in your classes')

@section('content')
{{-- Filter --}}
<div class="card mb-5">
    <form method="GET">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                class="input sm:flex-1" style="min-width:0;" placeholder="Search by name...">
            <select name="class_id" class="input sm:w-auto">
                <option value="">All My Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                @if(request()->hasAny(['search','class_id']))
                    <a href="{{ route('teacher.students') }}" class="btn-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if($students->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">ID</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Face Data</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Attendance Today</th>
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
                    <td class="py-3.5"><span class="badge-slate font-mono whitespace-nowrap">{{ $student->student_id }}</span></td>
                    <td class="py-3.5"><span class="badge-blue whitespace-nowrap">{{ $student->schoolClass->name }}</span></td>
                    <td class="py-3.5">
                        @if($student->face_encoded)
                            <span class="badge-green">✓ Encoded</span>
                        @else
                            <span class="badge-slate">Not encoded</span>
                        @endif
                    </td>
                    <td class="py-3.5">
                        @if($student->attendanceToday->count())
                            <span class="badge-green">Present</span>
                        @else
                            <span style="display:inline-flex;align-items:center;padding:2px 10px;border-radius:999px;font-size:11.5px;font-weight:500;background:#fee2e2;color:#dc2626;">Absent</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mt-4">{{ $students->links() }}</div>
    @else
        <div class="text-center py-16 text-slate-400">
            <p class="text-sm">No students found</p>
        </div>
    @endif
</div>
@endsection

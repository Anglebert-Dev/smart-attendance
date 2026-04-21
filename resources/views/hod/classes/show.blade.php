@extends('layouts.app')

@section('title', 'Class Details - ' . $class->name)
@section('page-title', 'Class Details')
@section('page-subtitle', $class->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Class Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="card">
            <h3 class="font-display font-bold text-slate-900 mb-4">Class Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Class Name</label>
                    <p class="text-sm font-medium text-slate-800">{{ $class->name }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Assigned Teacher</label>
                    <div class="flex items-center gap-2">
                        @if($class->teacher)
                            <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 text-xs font-bold">
                                {{ strtoupper(substr($class->teacher->name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-slate-700">{{ $class->teacher->name }}</span>
                        @else
                            <span class="text-sm text-slate-400 italic">No teacher assigned</span>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Student Count</label>
                    <span class="badge-blue">{{ $class->students->count() }} students</span>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Created At</label>
                    <p class="text-sm text-slate-600">{{ $class->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('hod.classes.index') }}" class="btn-secondary w-full justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Classes
        </a>
    </div>

    {{-- Students List --}}
    <div class="lg:col-span-2">
        <div class="card">
            <h3 class="font-display font-bold text-slate-900 mb-4">Students Enrolled</h3>
            @if($class->students->count())
                <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student ID</th>
                            <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($class->students as $student)
                        <tr>
                            <td class="py-3 text-sm font-mono text-slate-500">{{ $student->student_id }}</td>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-700 text-xs font-bold">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <p class="text-sm font-medium text-slate-800">{{ $student->name }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="text-center py-12 text-slate-400">
                    <p class="text-sm">No students enrolled in this class.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

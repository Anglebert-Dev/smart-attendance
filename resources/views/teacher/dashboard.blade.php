@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-title', 'My Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">My Classes</p>
            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['classes'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Assigned classes</p>
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Total Students</p>
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['students'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Across all classes</p>
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Today's Attendance</p>
            <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['today_attendance'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Marked today</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- My Classes --}}
    <div class="card">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-slate-900">My Classes</h3>
            <a href="{{ route('teacher.classes') }}" class="text-blue-600 text-xs hover:underline">View all</a>
        </div>
        @if($classes->count())
            <div class="space-y-3">
                @foreach($classes as $class)
                <div class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
                    <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800">{{ $class->name }}</p>
                        <p class="text-xs text-slate-400">{{ $class->students_count }} students</p>
                    </div>
                    <a href="{{ route('teacher.attendance') }}?class_id={{ $class->id }}" class="text-xs text-blue-600 hover:underline flex-shrink-0">View</a>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No classes assigned yet</p>
            </div>
        @endif
    </div>

    {{-- Recent Attendance --}}
    <div class="card">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-slate-900">Recent Attendance</h3>
            <a href="{{ route('teacher.attendance') }}" class="text-blue-600 text-xs hover:underline">View all</a>
        </div>
        @if($recentAttendance->count())
            <div class="space-y-3">
                @foreach($recentAttendance as $record)
                <div class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-700 text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($record->student->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $record->student->name }}</p>
                        <p class="text-xs text-slate-400">{{ $record->schoolClass->name }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="badge-green">Present</span>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $record->marked_at->format('H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No attendance records today</p>
            </div>
        @endif
    </div>
</div>
@endsection

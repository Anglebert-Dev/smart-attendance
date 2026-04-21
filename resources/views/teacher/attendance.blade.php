@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Attendance Records')
@section('page-subtitle', 'View attendance for your classes')

@section('content')
{{-- Filters --}}
<div class="card mb-5">
    <form method="GET">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                class="input sm:flex-1" style="min-width:0;" placeholder="Search student name...">
            <select name="class_id" class="input sm:w-auto">
                <option value="">All My Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
                class="input sm:w-auto">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('teacher.attendance') }}" class="btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if($records->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:540px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Status</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Time</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Method</th>
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($records as $record)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-xs font-bold" style="flex-shrink:0;">
                                {{ strtoupper(substr($record->student->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 text-sm whitespace-nowrap">{{ $record->student->name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $record->student->student_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5"><span class="badge-blue whitespace-nowrap">{{ $record->schoolClass->name }}</span></td>
                    <td class="py-3.5">
                        @if($record->status === 'present')
                            <span class="badge-green">Present</span>
                        @else
                            <span style="display:inline-flex;align-items:center;padding:2px 10px;border-radius:999px;font-size:11.5px;font-weight:500;background:#fee2e2;color:#dc2626;">Absent</span>
                        @endif
                    </td>
                    <td class="py-3.5 text-sm text-slate-600 whitespace-nowrap">{{ $record->marked_at->format('H:i') }}</td>
                    <td class="py-3.5">
                        <span class="badge-slate">{{ $record->method === 'face_recognition' ? '🤖 AI' : '✋ Manual' }}</span>
                    </td>
                    <td class="py-3.5 text-right">
                        <a href="{{ route('teacher.attendance.show', $record) }}" class="btn-secondary py-1 text-xs">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            <p class="text-sm">No attendance records for this filter</p>
        </div>
    @endif
</div>
@endsection

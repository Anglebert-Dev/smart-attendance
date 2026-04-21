@extends('layouts.app')

@section('title', 'Attendance Logs')
@section('page-title', 'Attendance Logs')
@section('page-subtitle', 'Real-time departmental attendance tracking')

@section('content')
<div class="card">
    @if($records->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Status</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Marked At</th>
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($records as $record)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-xs font-bold">
                                {{ strtoupper(substr($record->student->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 text-sm">{{ $record->student->name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $record->student->student_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 text-sm text-slate-600">
                        {{ $record->student->schoolClass->name ?? 'N/A' }}
                    </td>
                    <td class="py-3.5">
                        <span class="badge-green">Present</span>
                    </td>
                    <td class="py-3.5">
                        <div class="text-sm text-slate-700">{{ $record->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $record->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="py-3.5 text-right">
                        <a href="{{ route('hod.attendance.show', $record) }}" class="btn-secondary py-1 text-xs">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mt-6">
            {{ $records->links() }}
        </div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            <p class="font-medium text-slate-600 mb-1">No attendance records found</p>
        </div>
    @endif
</div>
@endsection

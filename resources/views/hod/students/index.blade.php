@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Student Directory')
@section('page-subtitle', 'List of all students across departments')

@section('content')
<div class="card">
    @if($students->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student ID</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Student Name</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Enrolled</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($students as $student)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5 text-sm font-mono text-slate-500">{{ $student->student_id }}</td>
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center text-green-700 text-sm font-bold">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <p class="font-medium text-slate-900 text-sm">{{ $student->name }}</p>
                        </div>
                    </td>
                    <td class="py-3.5">
                        <span class="badge-blue">{{ $student->schoolClass->name ?? 'N/A' }}</span>
                    </td>
                    <td class="py-3.5 text-xs text-slate-400">{{ $student->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="font-medium text-slate-600 mb-1">No students found</p>
        </div>
    @endif
</div>
@endsection

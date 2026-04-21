@extends('layouts.app')

@section('title', 'Classes')
@section('page-title', 'Class Monitoring')
@section('page-subtitle', 'Overview of all active classes')

@section('content')
<div class="card">
    @if($classes->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Class Name</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Teacher</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Students</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Created</th>
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($classes as $class)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 text-sm">{{ $class->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5">
                        @if($class->teacher)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-700">{{ $class->teacher->name }}</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Not assigned</span>
                        @endif
                    </td>
                    <td class="py-3.5">
                        <span class="badge-blue">{{ $class->students_count }} students</span>
                    </td>
                    <td class="py-3.5 text-xs text-slate-400">{{ $class->created_at->format('M d, Y') }}</td>
                    <td class="py-3.5 text-right">
                        <a href="{{ route('hod.classes.show', $class) }}" class="btn-secondary py-1.5 px-3 text-xs">View Details</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="font-medium text-slate-600 mb-1">No classes found</p>
        </div>
    @endif
</div>
@endsection

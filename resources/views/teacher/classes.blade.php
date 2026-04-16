@extends('layouts.app')

@section('title', 'My Classes')
@section('page-title', 'My Classes')
@section('page-subtitle', 'Classes assigned to you')

@section('content')
@if($classes->count())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($classes as $class)
        <div class="card hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="badge-blue">{{ $class->students_count }} students</span>
            </div>
            <h3 class="font-display font-bold text-slate-900 mb-1">{{ $class->name }}</h3>
            @if($class->description)
                <p class="text-xs text-slate-400 mb-4">{{ $class->description }}</p>
            @else
                <p class="text-xs text-slate-300 mb-4 italic">No description</p>
            @endif
            <div class="flex gap-2 mt-4 pt-4 border-t border-slate-100">
                <a href="{{ route('teacher.students') }}?class_id={{ $class->id }}" class="btn-secondary text-xs py-1.5 px-3 flex-1 justify-center">
                    View Students
                </a>
                <a href="{{ route('teacher.attendance') }}?class_id={{ $class->id }}" class="btn-primary text-xs py-1.5 px-3 flex-1 justify-center">
                    Attendance
                </a>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="card text-center py-16 text-slate-400">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
        <p class="font-medium text-slate-600 mb-1">No classes assigned</p>
        <p class="text-sm">Contact your admin to get assigned to a class</p>
    </div>
@endif
@endsection

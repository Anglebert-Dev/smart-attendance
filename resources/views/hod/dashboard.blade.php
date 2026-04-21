@extends('layouts.app')

@section('title', 'HOD Dashboard')
@section('page-title', 'HOD Review Dashboard')
@section('page-subtitle', 'Departmental Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Teachers</p>
            <div class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['total_teachers'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Staff members</p>
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Classes</p>
            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['total_classes'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Active sections</p>
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Students</p>
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['total_students'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Total enrollment</p>
    </div>

    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Today's Attendance</p>
            <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
        <p class="font-display text-3xl font-bold text-slate-900">{{ $stats['today_attendance'] }}</p>
        <p class="text-slate-400 text-xs mt-1">Logged today</p>
    </div>
</div>

<div class="card">
    <h3 class="font-display font-bold text-slate-900 mb-4">Quick Links</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('hod.teachers.index') }}" class="flex items-center p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
            <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Review Teachers</p>
                <p class="text-xs text-slate-500">View staff performance</p>
            </div>
        </a>
        <a href="{{ route('hod.classes.index') }}" class="flex items-center p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
            <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Class Monitoring</p>
                <p class="text-xs text-slate-500">Check class status</p>
            </div>
        </a>
        <a href="{{ route('hod.students.index') }}" class="flex items-center p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
            <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Student Directory</p>
                <p class="text-xs text-slate-500">View student records</p>
            </div>
        </a>
        <a href="{{ route('hod.attendance.index') }}" class="flex items-center p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
            <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Attendance Logs</p>
                <p class="text-xs text-slate-500">Global tracking</p>
            </div>
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Teachers')
@section('page-title', 'Staff Review')
@section('page-subtitle', 'List of all teachers in the department')

@section('content')
<div class="card">
    @if($teachers->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Teacher</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Email</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Classes</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($teachers as $teacher)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 text-sm font-bold">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                            <p class="font-medium text-slate-900 text-sm">{{ $teacher->name }}</p>
                        </div>
                    </td>
                    <td class="py-3.5 text-sm text-slate-600">{{ $teacher->email }}</td>
                    <td class="py-3.5">
                        @if($teacher->classes->count())
                            <div class="flex flex-wrap gap-1">
                                @foreach($teacher->classes as $class)
                                    <span class="badge-blue">{{ $class->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">No classes</span>
                        @endif
                    </td>
                    <td class="py-3.5 text-xs text-slate-400">{{ $teacher->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="font-medium text-slate-600 mb-1">No teachers found</p>
        </div>
    @endif
</div>
@endsection

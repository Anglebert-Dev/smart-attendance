@extends('layouts.app')

@section('title', 'Attendance Detail')
@section('page-title', 'Attendance Verification')
@section('page-subtitle', 'Verification for ' . $record->student->name)

@section('content')
<div class="max-width-4xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Student Summary --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card text-center py-8">
                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 text-3xl font-bold mx-auto mb-4">
                    {{ strtoupper(substr($record->student->name, 0, 1)) }}
                </div>
                <h3 class="font-display font-bold text-xl text-slate-900">{{ $record->student->name }}</h3>
                <p class="text-slate-500 text-sm font-mono mb-6">{{ $record->student->student_id }}</p>
                
                <div class="inline-flex items-center px-4 py-2 bg-slate-50 rounded-lg border border-slate-100">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest mr-2">Status:</span>
                    @if($record->status === 'present')
                        <span class="text-green-600 font-bold text-sm">PRESENT</span>
                    @else
                        <span class="text-red-600 font-bold text-sm">ABSENT</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('teacher.attendance') }}" class="btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Attendance
            </a>
        </div>

        {{-- Verification Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <h3 class="font-display font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Verification Audit
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Marked At</label>
                            <p class="text-slate-900 font-medium">{{ $record->marked_at->format('M d, Y') }}</p>
                            <p class="text-slate-500 text-sm">{{ $record->marked_at->format('H:i:s') }}</p>
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Method</label>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm font-medium text-slate-700">
                                    {!! $record->methodIcon() !!}&nbsp;{{ $record->methodLabel() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Period</label>
                            <p class="text-slate-900 font-medium">{{ $record->periodLabel() }}</p>
                            @if($record->period)
                                <p class="text-slate-500 text-sm">{{ $record->period->timeRangeLabel() }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Class</label>
                            <p class="text-slate-900 font-medium">{{ $record->schoolClass->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Face Detection Log --}}
            @if($record->method === 'face_recognition')
            <div class="card">
                <h3 class="font-display font-bold text-slate-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Face Detection Log
                </h3>

                <div class="flex items-center gap-4 mb-5">
                    <div class="flex items-center gap-2">
                        @php $count = $detections->count(); $required = \App\Services\AttendancePeriodService::REQUIRED_DETECTIONS; @endphp
                        @for($i = 1; $i <= $required; $i++)
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $i <= $count ? 'bg-violet-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                {{ $i }}
                            </div>
                        @endfor
                    </div>
                    <span class="text-sm font-medium {{ $count >= $required ? 'text-green-600' : 'text-red-500' }}">
                        {{ $count }} / {{ $required }} detections
                    </span>
                </div>

                @if($detections->isEmpty())
                    <p class="text-sm text-slate-400 italic">No face detection events recorded for this period.</p>
                @else
                    <ol class="space-y-2">
                        @foreach($detections as $i => $detection)
                        <li class="flex items-center gap-3 text-sm">
                            <span class="w-6 h-6 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-slate-700 font-mono">{{ $detection->detected_at->format('H:i:s') }}</span>
                            @if($i > 0)
                                <span class="text-slate-400 text-xs">+{{ $detection->detected_at->diffInSeconds($detections[$i-1]->detected_at) }}s</span>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

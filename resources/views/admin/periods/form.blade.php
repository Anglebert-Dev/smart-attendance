@extends('layouts.app')

@section('title', isset($period) ? 'Edit Period' : 'Create Period')
@section('page-title', isset($period) ? 'Edit Period' : 'Create Period')
@section('page-subtitle', 'Set start and end times for this class period')

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ isset($period) ? route('admin.periods.update', $period) : route('admin.periods.store') }}">
            @csrf
            @if(isset($period)) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $period->name ?? '') }}" required
                        class="input" placeholder="e.g. Period 1">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Start time <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', isset($period) ? substr($period->start_time, 0, 5) : '') }}" required class="input">
                        @error('start_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">End time <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time', isset($period) ? substr($period->end_time, 0, 5) : '') }}" required class="input">
                        @error('end_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Sort order <span class="text-red-500">*</span></label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $period->sort_order ?? 0) }}" required class="input">
                    @error('sort_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $period->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Active (used for attendance)</span>
                </label>
            </div>

            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit" class="btn-primary">
                    {{ isset($period) ? 'Update Period' : 'Create Period' }}
                </button>
                <a href="{{ route('admin.periods.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', isset($teacher) ? 'Edit Teacher' : 'Add Teacher')
@section('page-title', isset($teacher) ? 'Edit Teacher' : 'Add Teacher')
@section('page-subtitle', isset($teacher) ? 'Update teacher account' : 'Create a new teacher account')

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST"
            action="{{ isset($teacher) ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}">
            @csrf
            @if(isset($teacher)) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $teacher->name ?? '') }}" required
                        class="input" placeholder="Mr. John Smith">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $teacher->email ?? '') }}" required
                        class="input" placeholder="teacher@school.edu">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Password {{ isset($teacher) ? '(leave blank to keep current)' : '' }}
                        @if(!isset($teacher))<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="password" name="password" {{ !isset($teacher) ? 'required' : '' }}
                        class="input" placeholder="••••••••">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                @if(!isset($teacher))
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                        class="input" placeholder="••••••••">
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isset($teacher) ? 'Update Teacher' : 'Create Teacher Account' }}
                </button>
                <a href="{{ route('admin.teachers.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

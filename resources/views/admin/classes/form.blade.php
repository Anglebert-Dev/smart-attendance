@extends('layouts.app')

@section('title', isset($class) ? 'Edit Class' : 'Create Class')
@section('page-title', isset($class) ? 'Edit Class' : 'Create Class')
@section('page-subtitle', isset($class) ? 'Update class details' : 'Add a new class to the system')

@push('styles')
    @include('partials.select2')
@endpush

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ isset($class) ? route('admin.classes.update', $class) : route('admin.classes.store') }}">
            @csrf
            @if(isset($class)) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $class->name ?? '') }}" required
                        class="input" placeholder="e.g. Grade 10 - Science">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        class="input resize-none" placeholder="Optional description...">{{ old('description', $class->description ?? '') }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Department <span class="text-red-500">*</span></label>
                    @include('partials.department-select', ['selected' => old('department', $class->department ?? '')])
                    @error('department')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="text-xs text-slate-500 mb-2">Search and select one or more teachers for this class.</p>
                    <select id="teacher_ids" name="teacher_ids[]" class="input w-full" multiple>
                        @php
                            $selectedIds = array_map('intval', (array) old('teacher_ids', isset($class) ? $class->teachers->pluck('id')->all() : []));
                        @endphp
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ in_array($teacher->id, $selectedIds, true) ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_ids')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('teacher_ids.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isset($class) ? 'Update Class' : 'Create Class' }}
                </button>
                <a href="{{ route('admin.classes.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('#teacher_ids').select2({
            placeholder: 'Search teachers by name or email…',
            allowClear: true,
            width: '100%',
            closeOnSelect: false,
        });
    });
</script>
@endpush

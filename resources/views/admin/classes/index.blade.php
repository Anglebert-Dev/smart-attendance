@extends('layouts.app')

@section('title', 'Classes')
@section('page-title', 'Classes')
@section('page-subtitle', 'Create and manage classes')

@section('header-actions')
    <a href="{{ route('admin.classes.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Class
    </a>
@endsection

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
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Actions</th>
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
                                @if($class->description)
                                    <p class="text-xs text-slate-400">{{ Str::limit($class->description, 40) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5">
                        @if($class->teacher)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 text-xs font-bold">
                                    {{ strtoupper(substr($class->teacher->name, 0, 1)) }}
                                </div>
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
                    <td class="py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.classes.edit', $class) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger py-1.5 px-3 text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mt-4">{{ $classes->links() }}</div>
    @else
        <div class="text-center py-16 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="font-medium text-slate-600 mb-1">No classes yet</p>
            <p class="text-sm mb-4">Get started by creating your first class</p>
            <a href="{{ route('admin.classes.create') }}" class="btn-primary">Create Class</a>
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Class Periods')
@section('page-title', 'Class Periods')
@section('page-subtitle', 'School bell schedule — attendance is recorded per period')

@section('header-actions')
    <a href="{{ route('admin.periods.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Period
    </a>
@endsection

@section('content')
<div class="card mb-5">
    <p class="text-sm text-slate-600">
        When the camera marks a student, the system assigns the <strong>currently active period</strong> based on these times.
        Students with no sighting in a period are marked absent automatically
        <strong>2 minutes after each period ends</strong>, plus a final catch-all
        @if($catchAllTime = \App\Models\Period::catchAllScheduleTime())
            at <strong>{{ $catchAllTime }}</strong>
        @endif
        after the last period.
    </p>
</div>

<div class="card">
    @if($periods->count())
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:-4px;">
        <table class="w-full" style="min-width:580px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Order</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Name</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Time</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Absent job</th>
                    <th class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Status</th>
                    <th class="text-right text-xs font-semibold text-slate-400 uppercase tracking-wider pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($periods as $period)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5 text-sm text-slate-600">{{ $period->sort_order }}</td>
                    <td class="py-3.5 font-medium text-slate-900 text-sm">{{ $period->name }}</td>
                    <td class="py-3.5 text-sm text-slate-600">{{ $period->timeRangeLabel() }}</td>
                    <td class="py-3.5 text-xs text-slate-500">
                        Daily at {{ $period->absentMarkScheduleTime() }}
                    </td>
                    <td class="py-3.5">
                        @if($period->is_active)
                            <span class="badge-green">Active</span>
                        @else
                            <span class="badge-slate">Inactive</span>
                        @endif
                    </td>
                    <td class="py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.periods.edit', $period) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.periods.destroy', $period) }}" onsubmit="return confirm('Delete this period?')">
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
    @else
        <div class="text-center py-16 text-slate-400">
            <p class="font-medium text-slate-600 mb-1">No periods configured</p>
            <p class="text-sm mb-4">Run the seeder or create periods manually</p>
            <a href="{{ route('admin.periods.create') }}" class="btn-primary">Add Period</a>
        </div>
    @endif
</div>
@endsection

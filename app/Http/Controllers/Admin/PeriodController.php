<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = Period::orderBy('sort_order')->get();

        return view('admin.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.periods.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Period::create($data);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period created successfully.');
    }

    public function edit(Period $period)
    {
        return view('admin.periods.form', compact('period'));
    }

    public function update(Request $request, Period $period)
    {
        $period->update($this->validated($request));

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period updated successfully.');
    }

    public function destroy(Period $period)
    {
        $period->delete();

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'sort_order' => 'required|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['start_time'] = $data['start_time'] . ':00';
        $data['end_time']   = strlen($data['end_time']) === 5
            ? $data['end_time'] . ':00'
            : $data['end_time'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

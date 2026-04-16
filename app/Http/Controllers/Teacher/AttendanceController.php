<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classIds = SchoolClass::where('teacher_id', Auth::id())->pluck('id');
        $classes  = SchoolClass::where('teacher_id', Auth::id())->get();

        if ($request->class_id && !$classIds->contains((int) $request->class_id)) {
            abort(403, 'You do not have access to this class.');
        }

        $records = AttendanceRecord::with(['student', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->date,     fn($q) => $q->whereDate('marked_at', $request->date))
            ->latest('marked_at')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.attendance', compact('records', 'classes'));
    }
}

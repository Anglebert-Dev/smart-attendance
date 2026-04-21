<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Called by Python face recognition module to mark attendance.
     * POST /api/v1/attendance
     */
    public function markAttendance(Request $request)
    {
        // ── Validate ──────────────────────────────────────────────
        $data = $request->validate([
            'student_id' => 'required|string',
            'timestamp'  => 'required|string',
        ]);

        // ── Find Student (always eager load schoolClass) ──────────
        $student = Student::with('schoolClass')
            ->where('student_id', $data['student_id'])
            ->first();

        if (!$student) {
            return response()->json([
                'error'      => 'Student not found',
                'student_id' => $data['student_id'],
            ], 404);
        }

        $markedAt  = now()->parse($data['timestamp']);
        $classId   = $student->class_id;
        $className = $student->schoolClass->name ?? 'N/A';

        // ── Already marked TODAY? ─────────────────────────────────
        $alreadyMarked = AttendanceRecord::where('student_id', $student->id)
            ->where('class_id', $classId)
            ->whereDate('marked_at', $markedAt->toDateString())
            ->exists();

        if ($alreadyMarked) {
            return response()->json([
                'already_marked' => true,
                'message'        => "{$student->name} already marked present today.",
                'student'        => $student->name,
                'student_id'     => $student->student_id,
                'class'          => $className,
                'date'           => $markedAt->toDateString(),
            ], 200);
        }

        // ── Create Record ─────────────────────────────────────────
        AttendanceRecord::create([
            'student_id' => $student->id,
            'class_id'   => $classId,
            'status'     => 'present',
            'method'     => 'face_recognition',
            'marked_at'  => $markedAt,
        ]);

        return response()->json([
            'already_marked' => false,
            'success'        => true,
            'message'        => "Attendance marked for {$student->name}",
            'student'        => $student->name,
            'student_id'     => $student->student_id,
            'class'          => $className,
            'time'           => $markedAt->format('H:i:s'),
            'date'           => $markedAt->toDateString(),
        ], 200);
    }

    /**
     * Return all students with photos for encoding.
     * GET /api/v1/students/for-encoding
     */
    public function studentsForEncoding(Request $request)
    {
        $students = Student::with('schoolClass')
            ->whereNotNull('photo')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'student_id' => $s->student_id,
                'class'      => $s->schoolClass->name ?? 'N/A',
                'photo_url'  => asset('storage/' . $s->photo),
            ]);

        return response()->json(['students' => $students]);
    }

    /**
     * Mark student as face-encoded.
     * POST /api/v1/students/{id}/encoded
     */
    public function markEncoded(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update(['face_encoded' => true]);

        return response()->json(['success' => true, 'student' => $student->name]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Mark attendance for a student.
     * Called by Python recognize.py — auth handled by ApiKeyMiddleware.
     * POST /api/v1/attendance
     */
    public function markAttendance(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|string',          // unique student ID, not name
            'timestamp'  => 'required|string',
            'class_id'   => 'nullable|integer|exists:school_classes,id',
        ]);

        // Match by the unique student_id field — not name
        $student = Student::where('student_id', $data['student_id'])->first();

        if (!$student) {
            return response()->json([
                'error'      => 'Student not found',
                'student_id' => $data['student_id'],
            ], 404);
        }

        $markedAt = now()->parse($data['timestamp']);
        $classId  = $data['class_id'] ?? $student->class_id;

        // Duplicate guard — one entry per student per class per day
        $exists = AttendanceRecord::where('student_id', $student->id)
            ->where('class_id', $classId)
            ->whereDate('marked_at', $markedAt->toDateString())
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Already marked today',
                'student' => $student->name,
            ], 200);
        }

        AttendanceRecord::create([
            'student_id' => $student->id,
            'class_id'   => $classId,
            'status'     => 'present',
            'method'     => 'face_recognition',
            'marked_at'  => $markedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Attendance marked for {$student->name}",
            'student' => $student->name,
            'class'   => $student->schoolClass->name ?? 'N/A',
            'time'    => $markedAt->format('H:i:s'),
        ], 200);
    }

    /**
     * Return all students with photos for face encoding.
     * GET /api/v1/students/for-encoding
     */
    public function studentsForEncoding()
    {
        $students = Student::with(['schoolClass', 'photos'])
            ->whereHas('photos')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'student_id' => $s->student_id,
                'class'      => $s->schoolClass->name ?? 'N/A',
                'photo_urls' => $s->photos->map(fn($p) => $p->url())->values(),
            ]);

        return response()->json(['students' => $students]);
    }

    /**
     * Mark a student as face-encoded.
     * POST /api/v1/students/{id}/encoded
     */
    public function markEncoded($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['face_encoded' => true]);

        return response()->json(['success' => true, 'student' => $student->name]);
    }
}

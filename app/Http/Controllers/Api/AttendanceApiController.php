<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Services\AttendancePeriodService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceApiController extends Controller
{
    public function __construct(private AttendancePeriodService $periods) {}

    /**
     * Called by Python face recognition module to mark attendance.
     * POST /api/v1/attendance
     *
     * A student must be detected 3 times within the first 10 minutes of the
     * period to be marked present. Each call logs one detection; the 3rd
     * detection triggers the present record.
     */
    public function markAttendance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'required|string',
        ]);

        $student = Student::with('schoolClass')
            ->where('student_id', $data['student_id'])
            ->first();

        if (!$student) {
            return response()->json([
                'error'      => 'Student not found',
                'student_id' => $data['student_id'],
            ], 404);
        }

        $detectedAt = now();
        $classId    = $student->class_id;
        $className  = $student->schoolClass->name ?? 'N/A';
        $period     = $this->periods->resolveActivePeriod($detectedAt);

        if (!$period) {
            return response()->json([
                'already_marked' => true,
                'message'        => 'No active class period right now — attendance not recorded.',
                'student'        => $student->name,
                'student_id'     => $student->student_id,
                'class'          => $className,
                'date'           => $detectedAt->toDateString(),
            ]);
        }

        if ($this->periods->hasRecordFor($student->id, $classId, $period->id, $detectedAt)) {
            return response()->json([
                'already_marked' => true,
                'message'        => "{$student->name} already marked present for {$period->name}.",
                'student'        => $student->name,
                'student_id'     => $student->student_id,
                'class'          => $className,
                'period'         => $period->name,
                'date'           => $detectedAt->toDateString(),
            ]);
        }

        if (!$this->periods->isWithinDetectionWindow($period, $detectedAt)) {
            return response()->json([
                'already_marked' => false,
                'window_closed'  => true,
                'message'        => "{$student->name}: detection window closed for {$period->name}.",
                'student'        => $student->name,
                'student_id'     => $student->student_id,
                'class'          => $className,
                'period'         => $period->name,
                'date'           => $detectedAt->toDateString(),
            ]);
        }

        $this->periods->logDetection($student->id, $classId, $period->id, $detectedAt);

        $count    = $this->periods->countDetections($student->id, $classId, $period->id, $detectedAt);
        $required = AttendancePeriodService::REQUIRED_DETECTIONS;

        if ($count >= $required) {
            AttendanceRecord::create([
                'student_id' => $student->id,
                'class_id'   => $classId,
                'period_id'  => $period->id,
                'status'     => 'present',
                'method'     => 'face_recognition',
                'marked_at'  => $detectedAt,
            ]);

            return response()->json([
                'already_marked' => false,
                'success'        => true,
                'detections'     => $count,
                'message'        => "Attendance marked for {$student->name} ({$count}/{$required} detections)",
                'student'        => $student->name,
                'student_id'     => $student->student_id,
                'class'          => $className,
                'period'         => $period->name,
                'time'           => $detectedAt->format('H:i:s'),
                'date'           => $detectedAt->toDateString(),
            ]);
        }

        return response()->json([
            'already_marked' => false,
            'pending'        => true,
            'detections'     => $count,
            'required'       => $required,
            'message'        => "{$student->name} detected {$count}/{$required} for {$period->name}.",
            'student'        => $student->name,
            'student_id'     => $student->student_id,
            'class'          => $className,
            'period'         => $period->name,
            'date'           => $detectedAt->toDateString(),
        ]);
    }

    /**
     * Return all students with photos for encoding.
     * GET /api/v1/students/for-encoding
     */
    public function studentsForEncoding(Request $request)
    {
        $query = Student::with(['schoolClass', 'photos']);

        if ($request->boolean('unencoded')) {
            $query->where('face_encoded', false);
        }

        $students = $query->get()
            ->map(function ($s) {
                $urls = [];

                if ($s->photo) {
                    $urls[] = asset('storage/' . $s->photo);
                }

                foreach ($s->photos as $photo) {
                    $urls[] = $photo->url();
                }

                return [
                    'id'         => $s->id,
                    'name'       => $s->name,
                    'student_id' => $s->student_id,
                    'class'      => $s->schoolClass->name ?? 'N/A',
                    'photo_urls' => array_values(array_unique($urls)),
                ];
            })
            ->filter(fn($s) => count($s['photo_urls']) > 0)
            ->values();

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

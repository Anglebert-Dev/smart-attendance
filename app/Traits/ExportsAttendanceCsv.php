<?php

namespace App\Traits;

use Illuminate\Support\Facades\Response;

trait ExportsAttendanceCsv
{
    /**
     * Common method to stream attendance records as CSV.
     */
    protected function downloadCsv($records)
    {
        $fileName = 'attendance_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Student Name', 'Student ID', 'Class', 'Status', 'Date', 'Time', 'Method']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->student->name,
                    $record->student->student_id,
                    $record->schoolClass->name,
                    ucfirst($record->status),
                    $record->marked_at->format('Y-m-d'),
                    $record->marked_at->format('H:i:s'),
                    $record->methodLabel(),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

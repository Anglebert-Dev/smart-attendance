<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN method ENUM('face_recognition', 'manual', 'auto') NOT NULL DEFAULT 'face_recognition'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN method ENUM('face_recognition', 'manual') NOT NULL DEFAULT 'face_recognition'");
    }
};

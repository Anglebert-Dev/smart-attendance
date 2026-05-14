<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendance_records DROP CONSTRAINT IF EXISTS attendance_records_method_check');
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->string('method')->default('face_recognition')->change();
            });
            DB::statement("ALTER TABLE attendance_records ADD CONSTRAINT attendance_records_method_check CHECK (method IN ('face_recognition', 'manual', 'auto'))");
        } else {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->enum('method', ['face_recognition', 'manual', 'auto'])->default('face_recognition')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendance_records DROP CONSTRAINT IF EXISTS attendance_records_method_check');
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->string('method')->default('face_recognition')->change();
            });
            DB::statement("ALTER TABLE attendance_records ADD CONSTRAINT attendance_records_method_check CHECK (method IN ('face_recognition', 'manual'))");
        } else {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->enum('method', ['face_recognition', 'manual'])->default('face_recognition')->change();
            });
        }
    }
};

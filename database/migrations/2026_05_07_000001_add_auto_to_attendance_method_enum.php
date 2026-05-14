<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->enum('method', ['face_recognition', 'manual', 'auto'])->default('face_recognition')->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->enum('method', ['face_recognition', 'manual'])->default('face_recognition')->change();
        });
    }
};

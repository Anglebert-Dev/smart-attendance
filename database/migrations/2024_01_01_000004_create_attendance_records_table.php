<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->enum('method', ['face_recognition', 'manual'])->default('face_recognition');
            $table->timestamp('marked_at');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('attendance_records'); }
};

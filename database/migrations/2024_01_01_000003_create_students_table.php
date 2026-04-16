<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('student_id')->unique();
            $table->string('email')->nullable();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('photo')->nullable();
            $table->boolean('face_encoded')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('students'); }
};

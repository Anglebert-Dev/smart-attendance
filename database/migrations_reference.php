<?php
// ============================================================
// DATABASE MIGRATIONS
// Run: php artisan migrate
// ============================================================

// === 2024_01_01_000001_create_users_table.php ===
// Schema::create('users', function (Blueprint $table) {
//     $table->id();
//     $table->string('name');
//     $table->string('email')->unique();
//     $table->string('password');
//     $table->enum('role', ['admin', 'teacher'])->default('teacher');
//     $table->rememberToken();
//     $table->timestamps();
// });

// === 2024_01_01_000002_create_school_classes_table.php ===
// Schema::create('school_classes', function (Blueprint $table) {
//     $table->id();
//     $table->string('name');
//     $table->text('description')->nullable();
//     $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
//     $table->timestamps();
// });

// === 2024_01_01_000003_create_students_table.php ===
// Schema::create('students', function (Blueprint $table) {
//     $table->id();
//     $table->string('name');
//     $table->string('student_id')->unique();
//     $table->string('email')->nullable();
//     $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
//     $table->string('photo')->nullable();
//     $table->boolean('face_encoded')->default(false);
//     $table->timestamps();
// });

// === 2024_01_01_000004_create_attendance_records_table.php ===
// Schema::create('attendance_records', function (Blueprint $table) {
//     $table->id();
//     $table->foreignId('student_id')->constrained()->cascadeOnDelete();
//     $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
//     $table->enum('status', ['present', 'absent'])->default('present');
//     $table->enum('method', ['face_recognition', 'manual'])->default('face_recognition');
//     $table->timestamp('marked_at');
//     $table->timestamps();
// });

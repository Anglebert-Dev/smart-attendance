<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendance;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendance;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Classes
    Route::get('/classes',               [ClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/create',        [ClassController::class, 'create'])->name('classes.create');
    Route::post('/classes',              [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}/edit',  [ClassController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{class}',       [ClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{class}',    [ClassController::class, 'destroy'])->name('classes.destroy');

    // Students
    Route::get('/students',                                        [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create',                                 [StudentController::class, 'create'])->name('students.create');
    Route::post('/students',                                       [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit',                         [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}',                              [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}',                           [StudentController::class, 'destroy'])->name('students.destroy');
    Route::delete('/students/{student}/photos/{photo}',            [StudentController::class, 'destroyPhoto'])->name('students.photos.destroy');

    // Teachers
    Route::get('/teachers',                [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create',         [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers',               [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}',      [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}',   [TeacherController::class, 'destroy'])->name('teachers.destroy');

    // HODs
    Route::resource('hods', \App\Http\Controllers\Admin\HodController::class);

    // Attendance
    Route::get('/attendance', [AdminAttendance::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{record}', [AdminAttendance::class, 'show'])->name('attendance.show');

    // API Keys
    Route::get('/api-keys',                    [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys',                   [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::post('/api-keys/{apiKey}/revoke',   [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
    Route::delete('/api-keys/{apiKey}',        [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
});

Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');
    Route::get('/classes',   [TeacherClassController::class, 'index'])->name('classes');
    Route::get('/students',  [TeacherStudentController::class, 'index'])->name('students');
    Route::get('/attendance',[TeacherAttendance::class, 'index'])->name('attendance');
    Route::get('/attendance/{record}',[TeacherAttendance::class, 'show'])->name('attendance.show');
});

Route::middleware(['auth', 'hod'])->prefix('hod')->name('hod.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Hod\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/teachers',  [\App\Http\Controllers\Hod\TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/classes',   [\App\Http\Controllers\Hod\ClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [\App\Http\Controllers\Hod\ClassController::class, 'show'])->name('classes.show');
    Route::get('/students',  [\App\Http\Controllers\Hod\StudentController::class, 'index'])->name('students.index');
    Route::get('/attendance',[\App\Http\Controllers\Hod\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{record}',[\App\Http\Controllers\Hod\AttendanceController::class, 'show'])->name('attendance.show');
});

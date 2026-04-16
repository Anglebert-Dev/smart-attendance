<?php
// routes/api.php

use App\Http\Controllers\Api\AttendanceApiController;
use Illuminate\Support\Facades\Route;

   
Route::prefix('v1')
    ->middleware(['api.key', 'throttle:60,1'])
    ->group(function () {

    Route::post('/attendance', [AttendanceApiController::class, 'markAttendance']);

    Route::get('/students/for-encoding', [AttendanceApiController::class, 'studentsForEncoding']);

    Route::post('/students/{id}/encoded', [AttendanceApiController::class, 'markEncoded']);
});

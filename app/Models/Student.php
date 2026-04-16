<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['name', 'student_id', 'email', 'class_id', 'photo', 'face_encoded'];

    protected $casts = ['face_encoded' => 'boolean'];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function attendanceToday()
    {
        return $this->hasMany(AttendanceRecord::class)
            ->whereDate('marked_at', today());
    }

    public function photos()
    {
        return $this->hasMany(StudentPhoto::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDetection extends Model
{
    protected $fillable = ['student_id', 'class_id', 'period_id', 'detected_at'];

    protected $casts = [
        'detected_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}

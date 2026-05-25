<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = ['student_id', 'class_id', 'period_id', 'status', 'method', 'marked_at'];

    protected $casts = ['marked_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function periodLabel(): string
    {
        return $this->period?->name ?? '—';
    }

    public function methodLabel(): string
    {
        return match($this->method) {
            'face_recognition' => 'Face Recognition',
            'manual'           => 'Manual',
            'auto'             => 'Auto (System)',
            default            => ucfirst($this->method),
        };
    }

    public function methodIcon(): string
    {
        return match($this->method) {
            'face_recognition' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            'manual'           => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>',
            'auto'             => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            default            => '',
        };
    }

    public function methodColor(): string
    {
        return match($this->method) {
            'face_recognition' => 'indigo',
            'manual'           => 'slate',
            'auto'             => 'amber',
            default            => 'slate',
        };
    }
}

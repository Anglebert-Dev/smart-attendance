<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentPhoto extends Model
{
    protected $fillable = ['student_id', 'path'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $photo) {
            Storage::disk('public')->delete($photo->path);
        });
    }
}

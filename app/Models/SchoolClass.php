<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table    = 'school_classes';
    protected $fillable = ['name', 'description'];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'class_id', 'user_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'class_id');
    }

    public function scopeForTeacher(Builder $query, int $userId): Builder
    {
        return $query->whereHas('teachers', fn (Builder $q) => $q->where('users.id', $userId));
    }

    public function hasTeacher(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->teachers()->whereKey($userId)->exists();
    }

    // Append students count
    protected $withCount = ['students'];
}

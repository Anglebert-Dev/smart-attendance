<?php

namespace App\Models;

use App\Support\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table    = 'school_classes';
    protected $fillable = ['name', 'description', 'department'];

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

    public function scopeForDepartment(Builder $query, ?string $department): Builder
    {
        $code = Department::normalize($department);

        if (!$code) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('department', $code);
    }

    public function hasTeacher(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->teachers()->whereKey($userId)->exists();
    }

    public function departmentLabel(): string
    {
        return Department::label($this->department);
    }

    // Append students count
    protected $withCount = ['students'];
}

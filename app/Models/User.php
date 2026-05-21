<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'department'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed'];

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'user_id', 'class_id')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isHod(): bool
    {
        return $this->role === 'hod';
    }
}

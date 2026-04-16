<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin
        User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // Create a demo teacher
        User::firstOrCreate(
            ['email' => 'teacher@school.edu'],
            [
                'name'     => 'Demo Teacher',
                'password' => Hash::make('teacher123'),
                'role'     => 'teacher',
            ]
        );

        $this->command->info('✅ Default accounts created:');
        $this->command->info('   Admin   → admin@school.edu / admin123');
        $this->command->info('   Teacher → teacher@school.edu / teacher123');
    }
}

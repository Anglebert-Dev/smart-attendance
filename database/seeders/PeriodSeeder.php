<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            ['name' => 'Period 1', 'start_time' => '08:00:00', 'end_time' => '09:00:00', 'sort_order' => 1],
            ['name' => 'Period 2', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'sort_order' => 2],
            ['name' => 'Period 3', 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'sort_order' => 3],
            ['name' => 'Period 4', 'start_time' => '11:00:00', 'end_time' => '12:00:00', 'sort_order' => 4],
            ['name' => 'Period 5', 'start_time' => '13:00:00', 'end_time' => '14:00:00', 'sort_order' => 5],
            ['name' => 'Period 6', 'start_time' => '14:00:00', 'end_time' => '15:00:00', 'sort_order' => 6],
        ];

        foreach ($periods as $period) {
            Period::updateOrCreate(
                ['name' => $period['name']],
                array_merge($period, ['is_active' => true])
            );
        }
    }
}

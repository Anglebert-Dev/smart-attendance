<?php

use App\Models\Period;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Attendance absent-marking schedule (driven by admin-configured periods)
|--------------------------------------------------------------------------
| - One job shortly after each period ends (--period=id)
| - One catch-all after the last period ends (safety net if times change)
| Schedules are rebuilt from the DB on every `schedule:run` invocation.
*/
if (Schema::hasTable('periods')) {
    try {
        $periods = Period::query()->where('is_active', true)->orderBy('sort_order')->get();

        foreach ($periods as $period) {
            Schedule::command("attendance:mark-absent --period={$period->id}")
                ->dailyAt($period->absentMarkScheduleTime())
                ->name("attendance:mark-absent:period:{$period->id}");
        }

        if ($catchAll = Period::catchAllScheduleTime()) {
            Schedule::command('attendance:mark-absent')
                ->dailyAt($catchAll)
                ->name('attendance:mark-absent:catch-all');
        }
    } catch (\Throwable) {
        // DB may be unavailable during migrate/install — skip dynamic schedules.
    }
}

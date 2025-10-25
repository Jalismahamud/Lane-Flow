<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ExpireReports::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reports:expire')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Clear old soft-deleted reports (optional)
        $schedule->command('model:prune', ['--model' => 'App\\Models\\Report'])
            ->daily()
            ->at('02:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

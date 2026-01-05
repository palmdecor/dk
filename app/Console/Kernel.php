<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CleanupOldJobs;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CleanupOldJobs::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:cleanup-old-jobs')->daily();
    }

    protected function commands(): void
    {
        //
    }
}

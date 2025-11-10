<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Helpers\SemesterHelper;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('auto:create-schoolyear')
            ->timezone('Asia/Jakarta');

        $schedule->call(function () {
            SemesterHelper::commitSemester();
        })->dailyAt('00:00')
          ->timezone('Asia/Jakarta');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}

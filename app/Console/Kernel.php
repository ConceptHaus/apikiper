<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\SendNotifications::class,
        '\App\Console\Commands\SendReminders',
        '\App\Console\Commands\SendNotifications'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        
        //$schedule->command('inspire')->everyMinute();

        //$schedule->command('inactivity_notifications:send')->everyMinute()->appendOutputTo(storage_path('logs/inactivity_notifications.log'));

        $schedule->command('alerts_notifications:send')->everyMinute()
        ->appendOutputTo(storage_path('logs/alerts_notifications.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

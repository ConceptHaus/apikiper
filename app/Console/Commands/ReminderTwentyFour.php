<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReminderTwentyFour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:twentyfour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Reminder 24hr';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reminder = new \App\AppointmentReminders\TwentyFourReminder();
        $reminder->sendReminders();
    }
}

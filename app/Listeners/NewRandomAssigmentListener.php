<?php

namespace App\Listeners;

use App\Events\NewRandomAssigment;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class NewRandomAssigmentListener
{
    public function __construct()
    {

    }

    public function handle($event)
    {
        $activity = $event->evento;
        $random_user = array_rand($activity['colaboradores'],1);

        dd($random_user);

    }
}
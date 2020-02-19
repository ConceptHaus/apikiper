<?php

namespace App\Listeners;

use App\Modelos\User;
use App\Modelos\Prospecto\CatFuente;
use App\Modelos\Prospecto\ColaboradorProspecto;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use App\Mail\NewLeadAdmin;


use Mailgun;
use DB;

class DataAdmin{}
class NewLeadListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  NewLead  $event
     * @return void
     */
    public function handle($event)
    {
        $prospecto = $event->evento;
        $this->sendMail($prospecto);
        
    }

    public function sendMail($prospecto){
        $data = new DataAdmin;
        $data->admins = User::where('super_admin',1)->get();
        $data->prospecto = $prospecto;
        $data->fuente =CatFuente::find($data->prospecto->fuente);
        Mail::to($data->admins)->send(new NewLeadAdmin($data));
    }
}
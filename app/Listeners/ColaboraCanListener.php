<?php

namespace App\Listeners;

use App\Events\CoCan;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class ColaboraCanListener{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
        
    }

    /**
     * Handle the event.
     *
     * @param  CoCan  $event
     * @return void
     */
    public function handle($event){

        $actividad = $event->evento;
        $data['asunto'] = 'Bienvenido a Colabora Cancún';
        $data['email_de'] = 'ian@co-labora.mx';
        $data['nombre_de'] = 'Ian Quintanilla';

        $data['nombre_prospecto'] = $actividad->nombre;
        $data['apellido_prospecto'] = $actividad->apellido;
        $data['email_prospecto'] = $actividad->correo;

        Mailgun::send('mailing.prospectos.welcome_can',$data, function($message) use ($data){
            $message->from($data['email_de'],$data['nombre_de']);
            $message->subject($data['asunto']);
            $message->to($data['email_prospecto']);
            $message->trackOpens(true);
            $message->tag('colabora_cancun');
        });
    }
}
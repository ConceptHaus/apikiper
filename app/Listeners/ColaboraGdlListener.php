<?php

namespace App\Listeners;

use App\Events\CoGdl;

use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class ColaboraGdlListener{
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
     * @param  CoGdl  $event
     * @return void
     */
    public function handle($event){
        $actividad = $event->evento;
        $data['asunto'] = 'Bienvenido a Colabora Guadalajara';
        $data['email_de'] = 'jose@co-labora.mx';
        $data['nombre_de'] = 'José Luis Fernández';

        $data['nombre_prospecto'] = $actividad->nombre;
        $data['apellido_prospecto'] = $actividad->apellido;
        $data['email_prospecto'] = $actividad->correo;

        Mailgun::send('mailing.prospectos.welcome_gdl',$data, function($message) use ($data){
            $message->from($data['email_de'],$data['nombre_de']);
            $message->subject($data['asunto']);
            $message->to($data['email_prospecto']);
            $message->trackOpens(true);
            $message->tag('colabora_guadalajara');
        });
    }
}
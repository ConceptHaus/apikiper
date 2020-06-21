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
        $data['asunto'] = '¡Saludos de Colabora Guadalajara! 😁';
        $data['email_de'] = 'paola@co-labora.mx';
        $data['nombre_de'] = 'Paola Duarte';

        $data['nombre_prospecto'] = $actividad->nombre;
        $data['apellido_prospecto'] = $actividad->apellido;
        $data['email_prospecto'] = $actividad->correo;

        $data['nombre_p'] = $actividad->nombre;
        $data['apellido_p'] = $actividad->apellido;
        $data['correo_p'] = $actividad->correo;
        $data['empresa_p'] = $actividad->detalle_prospecto->empresa;
        $data['telefono_p'] = $actividad->detalle_prospecto->telefono;
        $data['mensaje_p'] = $actividad->detalle_prospecto->nota;
        $data['campaign_p'] = (isset($actividad->campaign->utm_campaign) ? $actividad->campaign->utm_campaign : 'orgánico');
        $data['term_p'] = (isset($actividad->campaign->utm_term) ? $actividad->campaign->utm_term : 'orgánico');

        Mailgun::send('mailing.template_newlead',$data, function($message) use ($data){
           $message->from('activity@kiper.app','Kiper'); 
           $message->subject('Tienes un nuevo prospecto 😁 🎉');
           $message->to($data['email_de'],$data['nombre_de']);
           $message->trackOpens(true);
           $message->tag('new_lead_colabora');
        });

        Mailgun::send('mailing.prospectos.welcome_gdl',$data, function($message) use ($data){
            $message->from($data['email_de'],$data['nombre_de']);
            $message->subject($data['asunto']);
            $message->to($data['email_prospecto']);
            $message->trackOpens(true);
            $message->tag('colabora_guadalajara');
        });
    }
}
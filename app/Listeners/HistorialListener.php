<?php

namespace App\Listeners;

use App\Events\Historial;
use App\Modelos\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mailgun;

class HistorialListener
{
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
     * @param  Historial  $event
     * @return void
     */
    public function handle($event)
    {
        $actividad = $event->evento;
        $super_admin = User::where('super_admin', '=', 1)->first();
        // if( $super_admin ) {
        //     $data['nombre_causante'] = $actividad->causer->nombre. ' ' . $actividad->causer->apellido;
        //     $data['correo_causante'] = $actividad->causer->email;
        //     $data['actividad'] = $actividad->properties['accion'] . ' ' . $actividad->log_name;
        //     $data['fecha'] = $actividad->created_at;
        //     $data['email_para'] = $super_admin->email;
        //     $data['nombre_para'] = $super_admin->nombre . ' ' . $super_admin->apellido;
        //     $data['asunto'] = 'Actualización en Kiper!';
        //     $data['email_de'] = 'activity@kiper.app';
        //     $data['nombre_de'] = 'Actividad de Kiper';
        //     Mailgun::send('mailing.mailSuperAdmin', $data, function ($message) use ($data){
        //         $message->from($data['email_de'],$data['nombre_de']);
        //         $message->subject($data['asunto']);
        //         $message->to($data['email_para'],$data['nombre_para']);  
        //     });
        // }
    }
}

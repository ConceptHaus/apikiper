<?php

namespace App\Listeners;

use App\Events\NewLead;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class NewLeadListener
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
     * @param  NewLead  $event
     * @return void
     */
    public function handle($event)
    {
        $actividad = $event->evento;
        $admins = DB::table('users')->where('is_admin',1)->get();
        $array_admins = array();
        //echo $admins;
        foreach ($admins as $admin) {
            array_push($array_admins,[$admin->email=>['name'=>$admin->nombre.' '.$admin->apellido]]);
        }
        //Array de admins

        if($admins){

            $data['email'] = $array_admins;
            $data['asunto'] = 'Tienes un nuevo lead 😁 🎉';
            $data['email_de'] = 'activity@kiper.io';
            $data['nombre_de'] = 'Kiper';

            //echo $actividad['nombre'];
           // echo $actividad->nombre;

            $data['nombre_p'] = $actividad['nombre'];
            $data['apellido_p'] = $actividad['apellido'];
            $data['correo_p'] = $actividad['correo'];
            $data['empresa_p'] = $actividad['empresa'];
            $data['telefono_p'] = $actividad['telefono'];
            $data['mensaje_p'] = $actividad['mensaje'];
            $data['campaign_p'] = $actividad['utm_campaign'];
            $data['term_p'] = $actividad['utm_term'];



            //Template
            //Funcion for para array de admins

            Mailgun::send('mailing.template_newlead',$data, function($message) use ($data){
                $message->from($data['email_de'],$data['nombre_de']);
                $message->subject($data['asunto']);
                foreach($data['email'] as $to_){
                    $message->to($to_);
                }
                $message->trackOpens(true);
                $message->tag('new_lead');
            });
        }
    }
}
<?php

namespace App\Listeners;

use App\Events\AssignProspecto;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Twilio\Rest\Client;

use Mailgun;
use DB;

class AssignProspectoListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
        {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');
            $this->sendingNumber = env('TWILIO_NUMBER');

            $this->twilioClient = new Client($accountSid, $authToken);
            
        }

    /**
     * Handle the event.
     *
     * @param  NewLead  $event
     * @return void
     */
    public function handle($event)
    {
        $data = $event->evento['prospecto'];
        $user = $event->evento['colaborador'];
        $mail_data['email'] = $user->email;
        $mail_data['nombre'] = $user->nombre;
        $mail_data['asunto'] = 'Te han asignado un prospecto 😁 🎉';
        $mail_data['email_de'] = 'activity@kiper.app';
        $mail_data['nombre_de'] = 'Kiper';

        $mail_data['nombre_p'] = $data->nombre;
        $mail_data['apellido_p'] = $data->apellido;
        $mail_data['correo_p'] = $data->correo;
        $mail_data['empresa_p'] = $data->detalle_prospecto->empresa;
        $mail_data['telefono_p'] = $data->detalle_prospecto->telefono;
        $mail_data['mensaje_p'] = $data->detalle_prospecto->nota;
        
        Mailgun::send('mailing.template_assignlead',$mail_data, function($message) use ($mail_data){
            $message->from($mail_data['email_de'],$mail_data['nombre_de']);
            $message->subject($mail_data['asunto']);
            $message->to($mail_data['email']);
            $message->trackOpens(true);
            $message->tag('assign_lead');
        });

        if(isset($user->detalle) && $user->detalle->celular){
            $this->twilioClient->messages->create(
                '+52'.$user->detalle->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Te han asignado un prospecto -- Nombre: '.$mail_data['nombre_p'].' '.$mail_data['apellido_p'].' Correo: '.$mail_data['correo_p'].' Telefono: '.$mail_data['telefono_p']
                ));
        }
        
    }
}
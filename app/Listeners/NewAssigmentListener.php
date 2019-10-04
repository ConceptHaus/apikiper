<?php 

namespace App\Listeners;

use App\Events\NewAssigment;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class NewAssigmentListener
{
    public function __construct()
    {

    }

    public function handle($event)
    {
        $activity = $event->evento;
        $colaboradores = array();
        if(is_array($activity['colaboradores'])){
            foreach($activity['colaboradores'] as $colaborador){
                $user = User::where('id',$colaborador)->first();
                array_push($colaboradores, $user->email);
            }
        }else{
            $user = User::where('id',$activity['colaboradores'])->first();
            array_push($colaboradores, $user->email);
        }
        
        //dd($colaboradores);
        
        if(count($activity) > 0){
            
            $data['email'] = $colaboradores;
            $data['asunto'] = 'Tienes un nuevo prospecto 😁 🎉';
            $data['email_de'] = 'activity@kiper.io';
            $data['nombre_de'] = 'Kiper';

            $data['nombre_p'] = $activity['prospecto']->nombre;
            $data['apellido_p'] = $activity['prospecto']->apellido;
            $data['correo_p'] = $activity['prospecto']->correo;
            $data['empresa_p'] = $activity['prospecto']->detalle_prospecto->empresa;
            $data['telefono_p'] = $activity['prospecto']->detalle_prospecto->telefono;
            $data['mensaje_p'] = $activity['prospecto']->detalle_prospecto->nota;
            $data['campaign_p'] = (isset($activity['prospecto']->campaign->utm_campaign) ? $activity['prospecto']->campaign->utm_campaign : 'orgánico');
            $data['term_p'] = (isset($activity['prospecto']->campaign->utm_term) ? $activity['prospecto']->campaign->utm_term : 'orgánico');

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
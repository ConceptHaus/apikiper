<?php

namespace App\Listeners;

use App\Events\NewLead;
use App\Modelos\User;
use App\Modelos\Prospecto\CatFuente;
use App\Modelos\Prospecto\ColaboradorProspecto;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Twilio\Rest\Client;


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
        $actividad = $event->evento;
        $admins = DB::table('users')->where('super_admin',1)->get();
        $array_admins = array();
        //echo $admins;
        foreach ($admins as $admin) {
            array_push($array_admins,[$admin->email=>['name'=>$admin->nombre.' '.$admin->apellido]]);
        }
        //Array de admins

        if($admins){
            $fuente = CatFuente::find($actividad->fuente);
            $assignment = ColaboradorProspecto::where('id_prospecto',$actividad->id_prospecto)->first() ?? 'Sin propietario';
             //User::where('id',$assignment->id_colaborador)->first() ?? 'Sin propietario';
            $data = array(
                'email'=>$array_admins,
                'fuente'=>$fuente->nombre,
                'asunto'=>"Nuevo prospecto vía {$fuente->nombre} 😁 🎉",
                'email_de'=>'activity@kiper.io',
                'nombre_de'=>'Kiper',
                'nombre_p'=>$actividad->nombre,
                'apellido_p'=>$actividad->apellido,
                'correo_p'=>$actividad->correo,
                'empresa_p'=>$actividad->detalle_prospecto->empresa,
                'telefono_p'=>$actividad->detalle_prospecto->telefono,
                'mensaje_p'=>$actividad->detalle_prospecto->nota,
                'campaign_p'=>(isset($actividad->campaign->utm_campaign) ? $actividad->campaign->utm_campaign : 'orgánico'),
                'term_p'=>(isset($actividad->campaign->utm_term) ? $actividad->campaign->utm_term : 'orgánico'),
                'asignacion_p'=>User::where('id',$assignment->id_colaborador)->first() ?? 'Sin propietario'
            );
            // $data['email'] = $array_admins;
            // $data['fuente'] = $fuente->nombre;
            // $data['asunto'] = "Nuevo prospecto vía {$fuente->nombre} 😁 🎉";
            // $data['email_de'] = 'activity@kiper.io';
            // $data['nombre_de'] = 'Kiper';


            // $data['nombre_p'] = $actividad->nombre;
            // $data['apellido_p'] = $actividad->apellido;
            // $data['correo_p'] = $actividad->correo;
            // $data['empresa_p'] = $actividad->detalle_prospecto->empresa;
            // $data['telefono_p'] = $actividad->detalle_prospecto->telefono;
            // $data['mensaje_p'] = $actividad->detalle_prospecto->nota;
            // $data['campaign_p'] = (isset($actividad->campaign->utm_campaign) ? $actividad->campaign->utm_campaign : 'orgánico');
            // $data['term_p'] = (isset($actividad->campaign->utm_term) ? $actividad->campaign->utm_term : 'orgánico');
            //$data['asignacion_p'] = 'Sin propietario';


            //Template
            //Funcion for para array de admins
            if(count($data['email']) > 0 ){
                
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
            foreach($admins as $admin){
                $user = User::find($admin->id);
                if(isset($user->detalle) && count($user->detalle->celular)==10){
                    $this->twilioClient->messages->create(
                    '+52'.$user->detalle->celular,
                    array(
                        "from" => $this->sendingNumber,
                        "body" => 'Kiper Leads | Nombre: '.$data['nombre_p'].' '.$data['apellido_p'].' Correo: '.$data['correo_p'].' Telefono: '.$data['telefono_p']
                    ));
                }
                
            }
            
        }
    }
}
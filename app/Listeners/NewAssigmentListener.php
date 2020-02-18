<?php 

namespace App\Listeners;

use App\Events\NewAssigment;
use App\Modelos\User;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Prospecto\CatFuente;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewLead;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Twilio\Rest\Client;
use Mailgun;
use DB;

class NewAssigmentListener
{
    public function __construct()
    {
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function handle($event)
    {   
        
        $data = $event->evento['prospecto'];
        
        $assigment_gfa = [
            'ejecutivo_1' => [
                'id'=>'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                'fechas'=>[
                    [Carbon::create(2020,2,17,19,0,1),Carbon::create(2020,2,18,19,0,0)],
                    [Carbon::create(2020,2,19,19,0,1),Carbon::create(2020,2,20,19,0,0)],
                    [Carbon::create(2020,2,24,19,0,1),Carbon::create(2020,2,25,19,0,0)],
                    [Carbon::create(2020,2,26,19,0,1),Carbon::create(2020,2,27,19,0,0)],
                    [Carbon::create(2020,2,28,19,0,1),Carbon::create(2020,2,29,19,0,0)],
                    [Carbon::create(2020,3,2,19,0,1),Carbon::create(2020,3,3,19,0,0)],
                    [Carbon::create(2020,3,4,19,0,1),Carbon::create(2020,3,5,19,0,0)],
                    [Carbon::create(2020,3,6,19,0,1),Carbon::create(2020,3,7,19,0,0)],
                    [Carbon::create(2020,3,7,19,0,1),Carbon::create(2020,3,8,19,0,0)],
                    [Carbon::create(2020,3,9,19,0,1),Carbon::create(2020,3,10,19,0,0)],
                    [Carbon::create(2020,3,11,19,0,1),Carbon::create(2020,3,12,19,0,0)],
                    [Carbon::create(2020,3,16,19,0,1),Carbon::create(2020,3,17,19,0,0)],
                    [Carbon::create(2020,3,18,19,0,1),Carbon::create(2020,3,19,19,0,0)],
                    [Carbon::create(2020,3,21,19,0,1),Carbon::create(2020,3,22,19,0,0)],
                    [Carbon::create(2020,3,23,19,0,1),Carbon::create(2020,3,24,19,0,0)],
                    [Carbon::create(2020,3,27,19,0,1),Carbon::create(2020,3,28,19,0,0)],
                    [Carbon::create(2020,3,28,19,0,1),Carbon::create(2020,3,29,19,0,0)],

                ]
                
            ],
            'ejecutivo_2'=>[
                'id'=>'5a07122b-c107-4e5f-b89d-7a147a8c3fe5',
                'fechas'=>[
                    [Carbon::create(2020,2,13,19,0,1),Carbon::create(2020,2,14,19,0,0)],
                    [Carbon::create(2020,2,14,19,0,1),Carbon::create(2020,2,15,19,0,0)],
                    [Carbon::create(2020,2,18,19,0,1),Carbon::create(2020,2,19,19,0,0)],
                    [Carbon::create(2020,2,23,19,0,1),Carbon::create(2020,2,24,19,0,0)],
                    [Carbon::create(2020,2,27,19,0,1),Carbon::create(2020,2,28,19,0,0)],
                    [Carbon::create(2020,2,29,19,0,1),Carbon::create(2020,3,1,19,0,0)],
                    [Carbon::create(2020,3,6,19,0,1),Carbon::create(2020,3,7,19,0,0)],
                    [Carbon::create(2020,3,7,19,0,1),Carbon::create(2020,3,8,19,0,0)],
                    [Carbon::create(2020,3,8,19,0,1),Carbon::create(2020,3,9,19,0,0)],
                    [Carbon::create(2020,3,10,19,0,1),Carbon::create(2020,3,11,19,0,0)],
                    [Carbon::create(2020,3,13,19,0,1),Carbon::create(2020,3,14,19,0,0)],
                    [Carbon::create(2020,3,14,19,0,1),Carbon::create(2020,3,15,19,0,0)],
                    [Carbon::create(2020,3,19,19,0,1),Carbon::create(2020,3,20,19,0,0)],
                    [Carbon::create(2020,3,20,19,0,1),Carbon::create(2020,3,21,19,0,0)],
                    [Carbon::create(2020,3,24,19,0,1),Carbon::create(2020,3,25,19,0,0)],
                    [Carbon::create(2020,3,26,19,0,1),Carbon::create(2020,3,27,19,0,0)],
                    [Carbon::create(2020,3,28,19,0,1),Carbon::create(2020,3,29,19,0,0)],
                    [Carbon::create(2020,3,29,19,0,1),Carbon::create(2020,30,8,19,0,0)],
                    [Carbon::create(2020,3,30,19,0,1),Carbon::create(2020,31,8,19,0,0)],
                ]       
            ],
            'ejecutivo_3'=>[
                'id'=>'09e78cf0-1bac-46ed-8945-5adb0f642840',
                'fechas'=>[
                    Carbon::createMidnightDate(2020,12,30),
                    Carbon::createMidnightDate(2020,1,3),
                    Carbon::createMidnightDate(2020,1,4),
                    Carbon::createMidnightDate(2020,1,7),
                    Carbon::createMidnightDate(2020,1,9),
                    Carbon::createMidnightDate(2020,1,12),
                    Carbon::createMidnightDate(2020,1,13),
                    Carbon::createMidnightDate(2020,1,15),
                    Carbon::createMidnightDate(2020,1,17),
                    Carbon::createMidnightDate(2020,1,18),
                    Carbon::createMidnightDate(2020,1,21),
                    Carbon::createMidnightDate(2020,1,23),
                    Carbon::createMidnightDate(2020,1,26),
                    Carbon::createMidnightDate(2020,1,27),
                    Carbon::createMidnightDate(2020,1,29),
                    Carbon::createMidnightDate(2020,1,31),
                ]       
            ],
            'ejecutivo_4'=>[
                'id'=>'fae9e0c4-78b5-478b-ba19-cf58a2593c21',
                'fechas'=>[
                    Carbon::createMidnightDate(2020,1,2),
                    Carbon::createMidnightDate(2020,1,5),
                    Carbon::createMidnightDate(2020,1,6),
                    Carbon::createMidnightDate(2020,1,8),
                    Carbon::createMidnightDate(2020,1,10),
                    Carbon::createMidnightDate(2020,1,11),
                    Carbon::createMidnightDate(2020,1,14),
                    Carbon::createMidnightDate(2020,1,16),
                    Carbon::createMidnightDate(2020,1,19),
                    Carbon::createMidnightDate(2020,1,20),
                    Carbon::createMidnightDate(2020,1,22),
                    Carbon::createMidnightDate(2020,1,24),
                    Carbon::createMidnightDate(2020,1,25),
                    Carbon::createMidnightDate(2020,1,28),
                    Carbon::createMidnightDate(2020,1,30),
                    
                ]       
            ], 
            'ejecutivo_5'=>[
                'id'=>'5ba84206-494d-45d4-b186-4e2c19c4c5fb',
                'fechas'=>[
                    [Carbon::create(2020,2,15,19,0,1),Carbon::create(2020,2,16,19,0,0)],
                    [Carbon::create(2020,2,16,19,0,1),Carbon::create(2020,2,17,19,0,0)],
                    [Carbon::create(2020,2,20,19,0,1),Carbon::create(2020,2,21,19,0,0)],
                    [Carbon::create(2020,2,21,19,0,1),Carbon::create(2020,2,22,19,0,0)],
                    [Carbon::create(2020,2,25,19,0,1),Carbon::create(2020,2,26,19,0,0)],
                    [Carbon::create(2020,3,1,19,0,1),Carbon::create(2020,3,2,19,0,0)],
                    [Carbon::create(2020,3,3,19,0,1),Carbon::create(2020,3,4,19,0,0)],
                    [Carbon::create(2020,3,5,19,0,1),Carbon::create(2020,3,6,19,0,0)],
                    [Carbon::create(2020,3,12,19,0,1),Carbon::create(2020,3,13,19,0,0)],
                    [Carbon::create(2020,3,13,19,0,1),Carbon::create(2020,3,14,19,0,0)],
                    [Carbon::create(2020,3,14,19,0,1),Carbon::create(2020,3,15,19,0,0)],
                    [Carbon::create(2020,3,15,19,0,1),Carbon::create(2020,3,16,19,0,0)],
                    [Carbon::create(2020,3,17,19,0,1),Carbon::create(2020,3,18,19,0,0)],
                    [Carbon::create(2020,3,20,19,0,1),Carbon::create(2020,3,21,19,0,0)],
                    [Carbon::create(2020,3,21,19,0,1),Carbon::create(2020,3,8,22,0,0)],
                    [Carbon::create(2020,3,22,19,0,1),Carbon::create(2020,3,23,19,0,0)],
                    [Carbon::create(2020,3,25,19,0,1),Carbon::create(2020,3,26,19,0,0)],
                    [Carbon::create(2020,3,27,19,0,1),Carbon::create(2020,3,28,19,0,0)],
                ]       
            ]       
        ];

        $date = Carbon::now();
        
        if($event->evento['desarrollo'] === 'polanco'){
            $adminstradores = User::where('rol',1)->get();
            foreach($adminstradores as $admin){
                //Mail::to($admin->email)->send($prospecto);
                $this->sendMail($admin->id,$event->evento['prospecto']);
            }
            
            $ejecutivo1 = $assigment_gfa['ejecutivo_1']['fechas'];

            foreach($ejecutivo1 as $key=>$value){
            
                if($date->between($ejecutivo1[$key][0],$ejecutivo1[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_1']['id'], $event->evento['prospecto']);
                    
                }
            }
            
            $ejecutivo2 = $assigment_gfa['ejecutivo_2']['fechas'];
                
                foreach($ejecutivo2 as $key=>$value){

                   if($date->between($ejecutivo2[$key][0],$ejecutivo2[$key][1],true)){
                      $this->assign($assigment_gfa['ejecutivo_2']['id'], $event->evento['prospecto']);
                  }
            }
            
            $ejecutivo5 = $assigment_gfa['ejecutivo_5']['fechas'];

            foreach($ejecutivo5 as $key=>$value){
                
                if($date->between($ejecutivo5[$key][0],$ejecutivo5[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_5']['id'], $event->evento['prospecto']);
                }
            }
        }else{
            $adminstradores = User::where('rol',2)->get();
            foreach($adminstradores as $admin){
                $this->sendMail($admin->id,$event->evento['prospecto']);
            }
            foreach($assigment_gfa['ejecutivo_3']['fechas'] as $dia){
            
                if($date->equalTo($dia)){
    
                    $this->assign($assigment_gfa['ejecutivo_3']['id'], $event->evento['prospecto']);

                }
            }
    
            foreach($assigment_gfa['ejecutivo_4']['fechas'] as $dia){
                
                if($date->equalTo($dia)){
    
                    $this->assign($assigment_gfa['ejecutivo_4']['id'], $event->evento['prospecto']);
                }
            }
        }
        
        
    }

    public function assign($id, $prospecto){

            $colaborador = User::where('id',$id)->first();
            //var_dump($colaborador, $prospecto);
            $pivot_col_pros = new ColaboradorProspecto();
            $pivot_col_pros->id_colaborador = $colaborador->id;
            $pivot_col_pros->id_prospecto = $prospecto->id_prospecto;
            $pivot_col_pros->save();

            $this->sendMail($id, $prospecto);
            $this->sendSMS($id, $prospecto);

    }

    public function sendMail($id, $prospecto){
        $colaborador = User::where('id',$id)->first();
        $fuente = CatFuente::find($prospecto->fuente);
        $data['email'] = $colaborador->email;
           
        $data['asunto'] = "Nuevo prospecto vía {$fuente->nombre} 😁 🎉";
        $data['email_de'] = 'activity@kiper.io';
        $data['nombre_de'] = 'Kiper';

        $data['nombre_p'] = $prospecto->nombre;
        $data['apellido_p'] = $prospecto->apellido;
        $data['correo_p'] = $prospecto->correo;
        $data['empresa_p'] = $prospecto->detalle_prospecto->empresa ?? 'not set';
        $data['telefono_p'] = $prospecto->detalle_prospecto->telefono;
        $data['mensaje_p'] = $prospecto->detalle_prospecto->nota ?? 'not set';
        $data['campaign_p'] = (isset($prospecto->campaign->utm_campaign) ? $prospecto->campaign->utm_campaign : 'orgánico');
        $data['term_p'] = (isset($prospecto->campaign->utm_term) ? $prospecto->campaign->utm_term : 'orgánico');

        Mailgun::send('mailing.template_newlead',$data, function($message) use ($data){
            $message->from($data['email_de'],$data['nombre_de']);
            $message->subject($data['asunto']);
            $message->to($data['email']);
            $message->trackOpens(true);
            $message->tag('new_lead');
        });
    }

    public function sendSMS($id,$prospecto){
        $colaborador = User::find($id);
            if(isset($colaborador->detalle) && $colaborador->detalle->celular){
                $this->twilioClient->messages->create(
                '+52'.$colaborador->detalle->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Kiper Leads | Nombre: '.$prospecto->nombre.' '.$prospecto->apellido.' Correo: '.$prospecto->correo.' Telefono: '.$prospecto->detalle_prospecto->telefono
                ));
            }
    }
}
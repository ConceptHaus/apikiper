<?php 

namespace App\Listeners;

use App\Events\NewAssigment;
use App\Modelos\User;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Prospecto\CatFuente;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        
        // $assigments = [
        //     Carbon::createMidnightDate(2020,2,1) =>[
        //         'polanco'=>[
        //             'ejecutivos'=>['c3d94d64-e966-44a8-9a03-6ed97e79688b']
        //         ],
        //         'napoles'=>[
        //             'ejecutivos'=>['5a07122b-c107-4e5f-b89d-7a147a8c3fe5']
        //         ]
        //     ]
        // ];
        $assigment_gfa = [
            'ejecutivo_1' => [
                'id'=>'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                'fechas'=>[
                    Carbon::createMidnightDate(2020,1,21),
                    Carbon::createMidnightDate(2020,1,23),
                    Carbon::createMidnightDate(2020,1,28),
                    Carbon::createMidnightDate(2020,1,30),
                    Carbon::createMidnightDate(2020,2,4),
                    Carbon::createMidnightDate(2020,2,6),
                    Carbon::createMidnightDate(2020,2,8),
                    Carbon::createMidnightDate(2020,2,11),
                    Carbon::createMidnightDate(2020,2,13),
                    Carbon::createMidnightDate(2020,2,18),
                    Carbon::createMidnightDate(2020,2,20),
                    Carbon::createMidnightDate(2020,2,25),
                    Carbon::createMidnightDate(2020,2,27),
                    Carbon::createMidnightDate(2020,2,29)
                ]
                
            ],
            'ejecutivo_2'=>[
                'id'=>'5a07122b-c107-4e5f-b89d-7a147a8c3fe5',
                'fechas'=>[
                    Carbon::createMidnightDate(2020,1,22),
                    Carbon::createMidnightDate(2020,1,25),
                    Carbon::createMidnightDate(2020,1,27),
                    Carbon::createMidnightDate(2020,1,31),
                    Carbon::createMidnightDate(2020,2,5),
                    Carbon::createMidnightDate(2020,2,9),
                    Carbon::createMidnightDate(2020,2,10),
                    Carbon::createMidnightDate(2020,2,14),
                    Carbon::createMidnightDate(2020,2,15),
                    Carbon::createMidnightDate(2020,2,19),
                    Carbon::createMidnightDate(2020,2,24),
                    Carbon::createMidnightDate(2020,2,28),
                    Carbon::createMidnightDate(2020,3,1),
                    
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
                    Carbon::createMidnightDate(2020,1,20),
                    Carbon::createMidnightDate(2020,1,24),
                    Carbon::createMidnightDate(2020,1,26),
                    Carbon::createMidnightDate(2020,1,29),
                    Carbon::createMidnightDate(2020,2,1),
                    Carbon::createMidnightDate(2020,2,3),
                    Carbon::createMidnightDate(2020,2,7),
                    Carbon::createMidnightDate(2020,2,12),
                    Carbon::createMidnightDate(2020,2,16),
                    Carbon::createMidnightDate(2020,2,17),
                    Carbon::createMidnightDate(2020,2,21),
                    Carbon::createMidnightDate(2020,2,22),
                    Carbon::createMidnightDate(2020,3,26),
                    
                ]       
            ]       
        ];

        $date = Carbon::today();
        
        if($event->evento['desarrollo'] === 'polanco'){
            $adminstradores = User::where('rol',1)->get();
            foreach($adminstradores as $admin){
                $this->sendMail($admin->id,$event->evento['prospecto']);
            }
            foreach($assigment_gfa['ejecutivo_1']['fechas'] as $dia){
            
                if($date->equalTo($dia)){
    
                    $this->assign($assigment_gfa['ejecutivo_1']['id'], $event->evento['prospecto']);
                    
                }
            }
    
            foreach($assigment_gfa['ejecutivo_2']['fechas'] as $dia){
                
                if($date->equalTo($dia)){
    
                    $this->assign($assigment_gfa['ejecutivo_2']['id'], $event->evento['prospecto']);
                }
            }
            
            foreach($assigment_gfa['ejecutivo_5']['fechas'] as $dia){
                
                if($date->equalTo($dia)){
    
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
<?php 

namespace App\Listeners;

use App\Events\NewAssigment;
use App\Modelos\User;
use App\Modelos\Prospecto\ColaboradorProspecto;

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
        

        $assigment_gfa = [
            'ejecutivo_1' => [
                'id'=>'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                'fechas'=>[
                    Carbon::createMidnightDate(2019,11,7),
                    Carbon::createMidnightDate(2019,11,8),
                    Carbon::createMidnightDate(2019,11,9),
                    Carbon::createMidnightDate(2019,11,12),
                    Carbon::createMidnightDate(2019,11,14),
                    Carbon::createMidnightDate(2019,11,17),
                    Carbon::createMidnightDate(2019,11,19),
                    Carbon::createMidnightDate(2019,11,21),
                    Carbon::createMidnightDate(2019,11,23),
                    Carbon::createMidnightDate(2019,11,26),
                    Carbon::createMidnightDate(2019,11,28),
                    Carbon::createMidnightDate(2019,11,29)
                ]
                
            ],
            'ejecutivo_2'=>[
                'id'=>'5a07122b-c107-4e5f-b89d-7a147a8c3fe5',
                'fechas'=>[
                    Carbon::createMidnightDate(2019,11,10),
                    Carbon::createMidnightDate(2019,11,11),
                    Carbon::createMidnightDate(2019,11,13),
                    Carbon::createMidnightDate(2019,11,15),
                    Carbon::createMidnightDate(2019,11,16),
                    Carbon::createMidnightDate(2019,11,20),
                    Carbon::createMidnightDate(2019,11,22),
                    Carbon::createMidnightDate(2019,11,24),
                    Carbon::createMidnightDate(2019,11,25),
                    Carbon::createMidnightDate(2019,11,27),
                    Carbon::createMidnightDate(2019,11,30),
                ]       
            ],
            'ejecutivo_3'=>[
                'id'=>'09e78cf0-1bac-46ed-8945-5adb0f642840',
                'fechas'=>[
                    Carbon::createMidnightDate(2019,11,7),
                    Carbon::createMidnightDate(2019,11,9),
                    Carbon::createMidnightDate(2019,11,11),
                    Carbon::createMidnightDate(2019,11,14),
                    Carbon::createMidnightDate(2019,11,15),
                    Carbon::createMidnightDate(2019,11,17),
                    Carbon::createMidnightDate(2019,11,21),
                    Carbon::createMidnightDate(2019,11,23),
                    Carbon::createMidnightDate(2019,11,25),
                    Carbon::createMidnightDate(2019,11,26),
                    Carbon::createMidnightDate(2019,11,28),
                    Carbon::createMidnightDate(2019,11,29),
                ]       
            ],
            'ejecutivo_4'=>[
                'id'=>'fae9e0c4-78b5-478b-ba19-cf58a2593c21',
                'fechas'=>[
                    Carbon::createMidnightDate(2019,11,8),
                    Carbon::createMidnightDate(2019,11,10),
                    Carbon::createMidnightDate(2019,11,12),
                    Carbon::createMidnightDate(2019,11,13),
                    Carbon::createMidnightDate(2019,11,16),
                    Carbon::createMidnightDate(2019,11,19),
                    Carbon::createMidnightDate(2019,11,20),
                    Carbon::createMidnightDate(2019,11,22),
                    Carbon::createMidnightDate(2019,11,24),
                    Carbon::createMidnightDate(2019,11,27),
                    Carbon::createMidnightDate(2019,11,30),
                ]       
            ]        
        ];

        $date = Carbon::today();
        
        if($event->evento['desarrollo'] === 'polanco'){
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
        }else{
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

            $data['email'] = $colaborador->email;
           
            $data['asunto'] = 'Tienes un nuevo prospecto 😁 🎉';
            $data['email_de'] = 'activity@kiper.io';
            $data['nombre_de'] = 'Kiper';

            $data['nombre_p'] = $prospecto->nombre;
            $data['apellido_p'] = $prospecto->apellido;
            $data['correo_p'] = $prospecto->correo;
            $data['empresa_p'] = $prospecto->detalle_prospecto->empresa;
            $data['telefono_p'] = $prospecto->detalle_prospecto->telefono;
            $data['mensaje_p'] = $prospecto->detalle_prospecto->nota;
            $data['campaign_p'] = (isset($prospecto->campaign->utm_campaign) ? $prospecto->campaign->utm_campaign : 'orgánico');
            $data['term_p'] = (isset($prospecto->campaign->utm_term) ? $prospecto->campaign->utm_term : 'orgánico');

            
            Mailgun::send('mailing.template_newlead',$data, function($message) use ($data){
                $message->from($data['email_de'],$data['nombre_de']);
                $message->subject($data['asunto']);
                $message->to($data['email']);
                $message->trackOpens(true);
                $message->tag('new_lead');
            });

            if(isset($colaborador->detalle) && count($colaborador->detalle->celular)==10){
                $this->twilioClient->messages->create(
                '+52'.$colaborador->detalle->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Kiper Leads | Nombre: '.$data['nombre_p'].' '.$data['apellido_p'].' Correo: '.$data['correo_p'].' Telefono: '.$data['telefono_p']
                ));
            }
    }
}
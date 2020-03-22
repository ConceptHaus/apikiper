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

class Data{}
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
            'ejecutivo_0'=>[
                'id'=>'261a34e0-de02-40c2-a5e9-bfdd55be193b',
                'nombre'=>'Patricia Alvarado',
                'fechas' => [
                    //[Carbon::create(2020,3,11,19,0,1),Carbon::create(2020,3,12,19,0,0)],
                ]
            ],
            'ejecutivo_1' => [
                'id'=>'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                'nombre'=>'Belora Abadi',
                'fechas'=>[
                    [Carbon::create(2020,3,20,19,0,1),Carbon::create(2020,3,21,19,0,0)],
                    //[Carbon::create(2020,3,21,19,0,1),Carbon::create(2020,3,22,19,0,0)],
                    [Carbon::create(2020,3,23,19,0,1),Carbon::create(2020,3,24,19,0,0)],
                    [Carbon::create(2020,3,27,19,0,1),Carbon::create(2020,3,28,19,0,0)],
                    [Carbon::create(2020,3,28,19,0,1),Carbon::create(2020,3,29,19,0,0)],

                ]
                
            ],
            'ejecutivo_2'=>[
                'id'=>'5a07122b-c107-4e5f-b89d-7a147a8c3fe5',
                'nombre'=>'Miguel Angel Velazquez',
                'fechas'=>[
                    // [Carbon::create(2020,3,20,19,0,1),Carbon::create(2020,3,21,19,0,0)],
                    // [Carbon::create(2020,3,24,19,0,1),Carbon::create(2020,3,25,19,0,0)],
                    // [Carbon::create(2020,3,26,19,0,1),Carbon::create(2020,3,27,19,0,0)],
                    // [Carbon::create(2020,3,28,19,0,1),Carbon::create(2020,3,29,19,0,0)],
                    // [Carbon::create(2020,3,29,19,0,1),Carbon::create(2020,3,30,19,0,0)],
                    // [Carbon::create(2020,3,30,19,0,1),Carbon::create(2020,3,31,19,0,0)],
                    // [Carbon::create(2020,4,2,19,0,1),Carbon::create(2020,4,3,19,0,0)],
                    // [Carbon::create(2020,4,4,19,0,1),Carbon::create(2020,4,5,19,0,0)],
                    // [Carbon::create(2020,4,6,19,0,1),Carbon::create(2020,4,7,19,0,0)],
                ]       
            ],
            'ejecutivo_3'=>[
                'id'=>'09e78cf0-1bac-46ed-8945-5adb0f642840',
                'nombre'=>'José Luis Vaca Ramos',
                'fechas'=>[
                    [Carbon::create(2020,3,20,19,0,1),Carbon::create(2020,3,21,19,0,0)],
                    [Carbon::create(2020,3,22,19,0,1),Carbon::create(2020,3,23,19,0,0)],
                    [Carbon::create(2020,3,24,19,0,1),Carbon::create(2020,3,25,19,0,0)],
                ]       
            ],
            'ejecutivo_4'=>[
                'id'=>'fae9e0c4-78b5-478b-ba19-cf58a2593c21',
                'nombre'=>'Gerardo Campuzano',
                'fechas'=>[
                    [Carbon::create(2020,3,19,19,0,1),Carbon::create(2020,3,20,19,0,0)],
                    [Carbon::create(2020,3,21,19,0,1),Carbon::create(2020,3,22,19,0,0)],
                    [Carbon::create(2020,3,23,19,0,1),Carbon::create(2020,3,24,19,0,0)],
                    
                ]       
            ], 
            'ejecutivo_5'=>[
                'id'=>'5ba84206-494d-45d4-b186-4e2c19c4c5fb',
                'nombre'=>'Alejandra Campos',
                'fechas'=>[
                    [Carbon::create(2020,3,21,19,0,1),Carbon::create(2020,3,22,19,0,0)],
                    [Carbon::create(2020,3,22,19,0,1),Carbon::create(2020,3,23,19,0,0)],
                    [Carbon::create(2020,3,25,19,0,1),Carbon::create(2020,3,26,19,0,0)],
                    [Carbon::create(2020,3,27,19,0,1),Carbon::create(2020,3,28,19,0,0)],
                ]       
            ]       
        ];

        $date = Carbon::now();
        $desarrollo = $event->evento['desarrollo']; 
        if($desarrollo === 'polanco'){
            
            $ejevutivo0 = $assigment_gfa['ejecutivo_0']['fechas'];

            foreach($ejevutivo0 as $key=>$value){
            
                if($date->between($ejevutivo0[$key][0],$ejevutivo0[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_0']['id'], $event->evento['prospecto'],$desarrollo);
                    
                }
            }

            $ejecutivo1 = $assigment_gfa['ejecutivo_1']['fechas'];

            foreach($ejecutivo1 as $key=>$value){
            
                if($date->between($ejecutivo1[$key][0],$ejecutivo1[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_1']['id'], $event->evento['prospecto'],$desarrollo);
                    
                }
            }
            
            $ejecutivo2 = $assigment_gfa['ejecutivo_2']['fechas'];
                
                foreach($ejecutivo2 as $key=>$value){

                   if($date->between($ejecutivo2[$key][0],$ejecutivo2[$key][1],true)){
                      $this->assign($assigment_gfa['ejecutivo_2']['id'], $event->evento['prospecto'],$desarrollo);
                  }
            }
            
            $ejecutivo5 = $assigment_gfa['ejecutivo_5']['fechas'];

            foreach($ejecutivo5 as $key=>$value){
                
                if($date->between($ejecutivo5[$key][0],$ejecutivo5[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_5']['id'], $event->evento['prospecto'],$desarrollo);
                }
            }
        }else if($desarrollo === 'napoles'){
            $ejecutivo3 = $assigment_gfa['ejecutivo_3']['fechas'];
            
            foreach($ejecutivo3 as $key=>$value){
            
                if($date->between($ejecutivo3[$key][0],$ejecutivo3[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_3']['id'], $event->evento['prospecto'],$desarrollo);

                }
            }
            $ejecutivo4 = $assigment_gfa['ejecutivo_4']['fechas'];
            foreach($ejecutivo4 as $key=>$value){
                
                if($date->between($ejecutivo4[$key][0],$ejecutivo4[$key][1],true)){
    
                    $this->assign($assigment_gfa['ejecutivo_4']['id'], $event->evento['prospecto'],$desarrollo);
                }
            }
        }
        
        
    }

    public function assign($id,$prospecto,$desarrollo){

            $colaborador = User::where('id',$id)->first();
            $pivot_col_pros = new ColaboradorProspecto();
            $pivot_col_pros->id_colaborador = $colaborador->id;
            $pivot_col_pros->id_prospecto = $prospecto->id_prospecto;
            $pivot_col_pros->save();
            $prospecto->desarrollo = $desarrollo;
            $this->sendMail($id, $prospecto);
            //$this->sendSMS($id, $prospecto);

    }

    public function sendMail($id, $prospecto){
        
        $data = new Data;
        $data->colaborador = User::where('id',$id)->first();
        $data->desarrollo = $prospecto->desarrollo;
        $data->prospecto = $prospecto;
        $data->fuente =CatFuente::find($data->prospecto->fuente);
        Mail::to($data->colaborador->email)->send(new NewLead($data));
        
    }

    // public function sendSMS($id,$prospecto){
    //     $colaborador = User::where('id',$id)->first();
    //         if(isset($colaborador->detalle) && $colaborador->detalle->celular){
    //             $this->twilioClient->messages->create(
    //             '+52'.$colaborador->detalle->celular,
    //             array(
    //                 "from" => $this->sendingNumber,
    //                 "body" => 'Kiper Leads | Nombre: '.$prospecto->nombre.' '.$prospecto->apellido.' Correo: '.$prospecto->correo.' Telefono: '.$prospecto->detalle_prospecto->telefono
    //             ));
    //         }
    // }
}
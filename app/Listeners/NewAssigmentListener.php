<?php 

namespace App\Listeners;

use App\Events\NewAssigment;
use App\Modelos\User;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Prospecto\CatFuente;
use App\Modelos\Prospecto;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewLead;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Twilio\Rest\Client;
use Mailgun;
use DB;
use Illuminate\Support\Arr;

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
        $desarrollo = $event->evento['desarrollo'];
        $date = Carbon::now();
        // Kiper Landmark
        if ($desarrollo === 'landmark') {
            echo('Kiper Landmark'. PHP_EOL);
            $assigment_landmark = [
                'ejecutivo_0'=>[
                    'id'=>'4f0b2f78-c6a0-4bab-9054-ede329cb5ce9',
                    'nombre'=>'Berenice Iturbide',
                    'fechas' => [
                        // Miércoles 03 marzo 10:00-3:00 PM
                        // Lunes 08 marzo 10:00-3:00 PM
                        [Carbon::create(2021,3,3,10,0,0),Carbon::create(2021,3,3,15,0,0)],
                        [Carbon::create(2021,3,8,10,0,0),Carbon::create(2021,3,8,15,0,0)],
                    ]
                ],
                'ejecutivo_1' => [
                    'id'=>'3d7bff59-b743-4949-bb51-51716e73a942',
                    'nombre'=>'Álvaro Sainz',
                    'fechas'=>[
                        // 01 Marzo 3:01 PM - 7:00 PM Lunes
                        // 09 Marzo 3:00 PM - 7:00 PM Martes
                        // 11 Marzo 3:00 PM - 7:00 PM Jueves
                        [Carbon::create(2021,3,1,15,0,1),Carbon::create(2021,3,1,19,0,0)],
                        [Carbon::create(2021,3,9,15,0,1),Carbon::create(2021,3,9,19,0,0)],
                        [Carbon::create(2021,3,11,15,0,1),Carbon::create(2021,3,11,19,0,0)],
                    ]
                    
                ],
                /*'ejecutivo_2'=>[
                    'id'=>'d32f23d4-d56b-4ccd-a892-a51c5399ee80',
                    'nombre'=>'Daniel Mosqueira',
                    'fechas'=>[
                        // Martes 16 Febrero 10 a 15
                        // Jueves 25 Febrero 15 a 19
                        [Carbon::create(2021,2,16,10,0,0),Carbon::create(2021,2,16,15,0,0)],
                        [Carbon::create(2021,2,25,15,0,1),Carbon::create(2021,2,25,19,0,0)],
                    ]       
                ],*/
                'ejecutivo_2'=>[
                    'id'=>'6fba4841-31cf-40f8-af8e-7a5544b81557',
                    'nombre'=>'José Jaime Flores',
                    'fechas'=>[
                        // 04 Marzo 3:00 PM - 7:00 PM Jueves
                        // 12 Marzo 3:00 PM - 7:00 PM Viernes
                        [Carbon::create(2021,3,4,15,0,1),Carbon::create(2021,3,4,19,0,0)],
                        [Carbon::create(2021,3,12,15,0,1),Carbon::create(2021,3,12,19,0,0)],                    
                    ]       
                ],
                'ejecutivo_3'=>[
                    'id'=>'51b9ddd4-262d-4d96-a8f9-655b10a38b86',
                    'nombre'=>'Gloria Macias',
                    'fechas'=>[
                        // 03 Marzo 3:00 PM - 7:00 PM Miércoles
                        // 10 Marzo 10:00 AM - 3:00 PM Miércoles
                        [Carbon::create(2021,3,3,15,0,1),Carbon::create(2021,3,3,19,0,0)],
                        [Carbon::create(2021,3,10,10,0,0),Carbon::create(2021,3,10,15,0,0)],                            
                    ]       
                ], 
                'ejecutivo_4'=>[
                    'id'=>'820fccc7-f20a-4505-8848-874d3a5d0944',
                    'nombre'=>'Laura Flores',
                    'fechas'=>[
                        // 02 Marzo 3:00 PM Martes
                        // 06 Marzo 10:00 AM Sábado
                        // 15 Marzo 3:00 PM Lunes
                        [Carbon::create(2021,3,2,15,0,1),Carbon::create(2021,3,2,19,0,0)],
                        [Carbon::create(2021,3,6,10,0,0),Carbon::create(2021,3,6,15,0,0)],
                        [Carbon::create(2021,3,15,15,0,1),Carbon::create(2021,3,15,19,0,0)]
                    ]       
                ],
                'ejecutivo_5'=>[
                    'id'=>'be1bbb44-8acf-41db-b61e-b1d53b7c8d42',
                    'nombre'=>'Mónica Sánchez',
                    'fechas'=>[
                        // 04 Marzo Jueves 10:00 AM
                        // 09 Marzo Martes 03:00 PM
                        // 13 Marzo Sábado 10:00 AM
                        [Carbon::create(2021,3,4,10,0,0),Carbon::create(2021,3,4,15,0,0)],
                        [Carbon::create(2021,3,9,15,0,1),Carbon::create(2021,3,9,19,0,0)],
                        [Carbon::create(2021,3,13,10,0,0),Carbon::create(2021,3,13,15,0,0)]
                    ]       
                ],
                'ejecutivo_6'=>[
                    'id'=>'16ab8dac-4083-4b8a-8d39-2c923688c810',
                    'nombre'=>'José Rentería',
                    'fechas'=>[
                        // 05 Marzo Viernes 10:00 AM
                        // 10 Marzo Miércoles 3:00 PM
                        // 15 Marzo Lunes 10:00 AM
                        [Carbon::create(2021,3,5,10,0,0),Carbon::create(2021,3,5,15,0,1)],
                        [Carbon::create(2021,3,10,15,0,1),Carbon::create(2021,3,10,19,0,0)],
                        [Carbon::create(2021,3,15,10,0,0),Carbon::create(2021,3,15,15,0,0)]
                    ]       
                ],
                'ejecutivo_7'=>[
                    'id'=>'c6092094-bd54-445e-848d-4bd3fb79bd7e',
                    'nombre'=>'Mauricio Montaño',
                    'fechas'=>[
                        // 02 Marzo Martes 10:00 AM
                        // 08 Marzo Lunes 3:00 PM
                        // 12 Marzo Viernes 10:00 AM
                        [Carbon::create(2021,3,2,10,0,0),Carbon::create(2021,3,2,15,0,0)],
                        [Carbon::create(2021,3,8,15,0,1),Carbon::create(2021,3,8,19,0,0)],
                        [Carbon::create(2021,3,12,10,0,0),Carbon::create(2021,3,12,15,0,0)]
                    ]       
                ],
                'ejecutivo_8'=>[
                    'id'=>'a5b1ff68-8a09-4392-a517-57f1da9a61f0',
                    'nombre'=>'Patricia Rivera',
                    'fechas'=>[
                        // 01 Marzo Lunes 10:00 AM
                        // 05 Marzo Viernes 3:00 PM
                        // 11 Marzo Jueves 10:00 AM
                        [Carbon::create(2021,3,1,10,0,0),Carbon::create(2021,3,15,15,0,0)],
                        [Carbon::create(2021,3,5,15,0,1),Carbon::create(2021,3,5,19,0,0)],
                        [Carbon::create(2021,3,11,10,0,0),Carbon::create(2021,3,11,15,0,0)]
                    ]       
                ],
            ];

            // Se realiza análisis de listado de ejecutivo vs fechas configuradas
            foreach($assigment_landmark as $index=>$item){
                ${"assigment_".$index} = $assigment_landmark[$index]['fechas'];
                foreach(${"assigment_".$index} as $key=>$value){
                    if($date->between(${"assigment_".$index}[$key][0],${"assigment_".$index}[$key][1],true)){
                        echo('Se asigno a: '.$assigment_landmark[$index]['nombre']. PHP_EOL);
                        $this->assign($assigment_landmark[$index]['id'], $event->evento['prospecto'],$desarrollo);
                    }
                }
            }
        } 
        // Kiper GFA
        else {
            echo('Kiper GFA');
            $polanco = [
                        'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                        '5ba84206-494d-45d4-b186-4e2c19c4c5fb',
                        '09e78cf0-1bac-46ed-8945-5adb0f642840',
                        '776150e5-0f8f-414f-a987-9e4d52522105'
                    ];
            $napoles = [
                        'c3d94d64-e966-44a8-9a03-6ed97e79688b',
                        '5ba84206-494d-45d4-b186-4e2c19c4c5fb',
                        '09e78cf0-1bac-46ed-8945-5adb0f642840',
                        'fae9e0c4-78b5-478b-ba19-cf58a2593c21',
                        '776150e5-0f8f-414f-a987-9e4d52522105'
    
            ];
            $random_broker_p=Arr::random($polanco);
            $random_broker_n=Arr::random($napoles);
            if($desarrollo === 'polanco'){
                
                $prospectos_today = DB::table('prospectos')
                                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                                ->where('etiquetas.nombre','like','%polanco%')
                                ->where('prospectos.deleted_at',null)
                                ->where('prospectos.fuente','!=',3)
                                ->groupBy('prospectos.id_prospecto')
                                ->get();
                if($prospectos_today->count() > 0){
                    $remainder = $prospectos_today->count() % 2;
                    echo 'Colaborador '.$random_broker_p.' ';
                    echo 'Total de leads '.$prospectos_today->count();
                    $this->assign($random_broker_p,$event->evento['prospecto'],$desarrollo);
                }
            }else if($desarrollo === 'napoles'){
                $prospectos_today = DB::table('prospectos')
                                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                                ->where('etiquetas.nombre','like','%napoles%')
                                ->where('prospectos.deleted_at',null)
                                ->where('prospectos.fuente','!=',3)
                                ->groupBy('prospectos.id_prospecto')
                                ->get();
                if($prospectos_today->count() > 0){
                    $remainder = $prospectos_today->count() % 2;
                    echo 'Colaborador '.$random_broker_n.' ';
                    echo 'Total de leads '.$prospectos_today->count();
                    $this->assign($random_broker_n,$event->evento['prospecto'],$desarrollo);
                }
            }
        }
    }

    public function assign($id,$prospecto,$desarrollo)
    {
        $colaborador = User::where('id',$id)->first();
        $pivot_col_pros = new ColaboradorProspecto();
        $pivot_col_pros->id_colaborador = $colaborador->id;
        $pivot_col_pros->id_prospecto = $prospecto->id_prospecto;
        $pivot_col_pros->save();
        $prospecto->desarrollo = $desarrollo;
        $this->sendMail($id, $prospecto);
        //$this->sendSMS($id, $prospecto);
    }

    public function sendMail($id, $prospecto)
    {
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
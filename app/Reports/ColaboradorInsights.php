<?php

namespace App\Reports;

use App\Modelos\User;

use Illuminate\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use DB;
use Mailgun;

class ColaboradorInsights{
     
    
    function __construct()
    {   
        
        $this->now = Carbon::now()->toDateTimeString();
        $this->last_week = Carbon::now()->subWeek()->toDateTimeString();
        $this->users = User::all();
   
    }
    
    public function sendReport(){
        
        $fin = Carbon::now()->format('l jS');
        $inicio = Carbon::now()->subWeek()->format('l jS');

        foreach ($this->users as $user) {
            $consulta = $this->consultaOportunidades($user->id,$this->last_week,$this->now);
            
            if(count($consulta)){

                $result = [];
                
                // foreach($consulta as $value){
                //     // $result['id'] = $value->id;
                //     // $result['color'] = $value->color;
                //     // $result['total'] = $value->total;
                //     // $result['status'] = $value->status;
                //     //array_push($result,$value->color,$value->total,$value->status);
                    
                // }
                

                $data =['result'=>$consulta,'user'=>$user, 'inicio'=>$inicio,'fin'=>$fin];
                // echo $inicio.' '.$fin;
                //echo '---'.$data['user']->email.' '.$data['result'].'---';
                Mailgun::send('mailing.reportes',$data, function($message) use ($data){
                    $message->from('activity@kiper.io','Kiper');
                    $message->subject('↗️ 📆 ¿Hacemos números? | Reporte Semanal');
                    //$message->to('57dced4c42-3a998f@inbox.mailtrap.io');
                    $message->to($data['user']->email,$data['user']->nombre.' '.$data['user']->apellido);
                });

            }else{
                 
                 
            }
           
        }

    }

    public function consultaOportunidades($id, $inicio, $fin){
        
            return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->where('colaborador_oportunidad.id_colaborador',$id)
                    ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get(); 
        
    }
}
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


        foreach ($this->users as $user) {
            $consulta = $this->consultaOportunidades($user->id,$this->now,$this->last_week);
            
            if(count($consulta)){

                $output = array_map(function ($object) { return $object; },$consulta->toArray());
                echo implode(', ', $output);
                // Mailgun::send('mailgin.report',$consulta, function($message) use ($consulta){
                //     $contacto->from('activity@kiper.io','Kiper');
                //     $contacto->subject('¿Hacemos números? | Reporte Semanal');
                //     $contacto->to($user->email,$user->nombre.' '.$user->apellido);
                // });

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
                    //->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get(); 
        
    }
}
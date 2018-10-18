<?php 

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;

use App\Modelos\Prospecto\Prospecto;

use DB;
use Mail;

class DataViewsController extends Controller
{
    public function dashboard(){
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial

        $oportuniades_cerradas = DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $colaboradores = DB::table('users')
                                ->join('colaborador_oportunidad','users.id','colaborador_oportunidad.id_colaborador')
                                ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->select('users.*')->orderBy('detalle_oportunidad.descripcion')->get();

        $origen_prospecto = DB::table('prospectos')
                                ->select(DB::raw('count(*) as fuente_count, fuente'))->groupBy('fuente')->get();

        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto','=',0)->count();
        
                                
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>$oportuniades_cerradas,
                'oportunidades_cotizadas'=>$oportunidades_cotizadas,
                'prospectos_sin_contactar'=>$prospectos_sin_contactar,
                'colaboradores'=>$colaboradores,
                'ingresos'=>'',
                'origen_prospecto'=>$origen_prospecto
            ]
        ]);

    }

    public function prospectos(){
        $total_prospectos = 
    }
}
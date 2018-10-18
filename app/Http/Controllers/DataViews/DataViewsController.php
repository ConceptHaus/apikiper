<?php 

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;


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

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>$oportuniades_cerradas,
                'oportunidades_cotizadas'=>$oportunidades_cotizadas,
                'prospectos_sin_contactar'=>'',
                'colaboradores'=>'',
                'ingresos'=>'',
                'origen_prospecto'=>''
            ]
        ]);

    }
}
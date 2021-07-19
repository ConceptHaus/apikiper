<?php

namespace App\Http\Repositories\Statistics;

use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\User;
use App\Http\Services\Funnel\FunnelService;
use App\Http\Services\UtilService;
use App\Http\Services\Statistics\StatisticsService;
use DB;

class StatisticsRep
{
    public static function ProspectosVsOportunidades($start_date, $end_date, $user_id=NULL)
    {
        $response = array();
        
        //Filter Days Prospectos
        $prospectos  =   Prospecto::select(DB::raw('DATE(prospectos.created_at) as date'), DB::raw('count(*) as total'))
                                    ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                    ->where('prospectos.created_at', '>=', $start_date)
                                    ->where('prospectos.created_at', '<=', $end_date);
        if(!is_null($user_id)){
            $prospectos = $prospectos->where('colaborador_prospecto.id_colaborador', $user_id);
        }
        $prospectos = $prospectos->groupBy('date')->get();

        //Filter Days Oportunidades
        $oportunidades   =   Oportunidad::select(DB::raw('DATE(oportunidades.created_at) as date'), DB::raw('count(*) as total'))
                                    ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')                            
                                    ->where('oportunidades.created_at', '>=', $start_date)
                                    ->where('oportunidades.created_at', '<=', $end_date);
        if(!is_null($user_id)){
            $oportunidades  =  $oportunidades->where('colaborador_oportunidad.id_colaborador', $user_id);
        }
        $oportunidades =  $oportunidades->groupBy('date')->get();

        //Oportunidades Cerradas 
        $oportunidades_cerradas =   Oportunidad::join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                                                ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')                            
                                                ->where('status_oportunidad.id_cat_status_oportunidad', 2)
                                                ->where('oportunidades.created_at', '>=', $start_date)
                                                ->where('oportunidades.created_at', '<=', $end_date);
        if(!is_null($user_id)){
            $oportunidades_cerradas  =  $oportunidades_cerradas->where('colaborador_oportunidad.id_colaborador', $user_id);
        }
        $oportunidades_cerradas =  $oportunidades_cerradas->count();

        //Oportunidades Cerradas by Fuente
        $oportunidades_by_fuente =   Oportunidad::select('cat_fuentes.id_fuente', 
                                                        DB::raw('count(*) as total_oportunidades'),
                                                        'cat_fuentes.nombre',
                                                        'cat_fuentes.url')
                                                ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                                                ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                                                ->join('oportunidad_prospecto', 'oportunidad_prospecto.id_oportunidad', 'oportunidades.id_oportunidad')
                                                ->join('prospectos', 'prospectos.id_prospecto', 'oportunidad_prospecto.id_prospecto')
                                                ->join('cat_fuentes', 'cat_fuentes.id_fuente', 'prospectos.fuente')
                                                ->where('status_oportunidad.id_cat_status_oportunidad', 2)
                                                ->where('oportunidades.created_at', '>=', $start_date)
                                                ->where('oportunidades.created_at', '<=', $end_date);
        if(!is_null($user_id)){
            $oportunidades_by_fuente  =  $oportunidades_by_fuente->where('colaborador_oportunidad.id_colaborador', $user_id);
        }
        $oportunidades_by_fuente =  $oportunidades_by_fuente->groupBy('cat_fuentes.id_fuente')
                                                            ->orderBy('total_oportunidades', 'DESC')
                                                            ->get();


        $response['prospectos_filter_dates']        = StatisticsService::makeDatesRangeArray($prospectos, $start_date, $end_date);
        $response['oportunidades_filter_dates']     = StatisticsService::makeDatesRangeArray($oportunidades, $start_date, $end_date);
        $response['oportunidades_cerradas']         = $oportunidades_cerradas;
        $response['oportunidades_by_fuente']        = $oportunidades_by_fuente;
        $response['porcentaje_exito']               = 90;

        return $response;
    }

    public static function SalesHistoryByColaborador($start_date, $end_date, $user_id=NULL)
    {
        
        $colaboradores = User::select(DB::raw('concat(users.nombre, " ", users.apellido) as nombre_colaborador'),
                    DB::raw('detalle_oportunidad.valor * detalle_oportunidad.meses as ventas'))
                    ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_colaborador', '=', 'users.id')
                    ->join('oportunidades', 'oportunidades.id_oportunidad', '=', 'colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', '=', 'oportunidades.id_oportunidad')
                    ->join('cat_status_oportunidad', 'cat_status_oportunidad.id_cat_status_oportunidad', '=', 'status_oportunidad.id_cat_status_oportunidad')
                    ->join('detalle_oportunidad', 'detalle_oportunidad.id_oportunidad', '=', 'oportunidades.id_oportunidad')
                    ->where('cat_status_oportunidad.id_cat_status_oportunidad', 2)
                    ->where('status_oportunidad.updated_at', '>=', $start_date . ' 00:00:00')
                    ->where('status_oportunidad.updated_at', '<=', $end_date . ' 23:59:59')
                    ->where(function ($query) use ($user_id) {
                        $query->when($user_id,  function ($query) use ($user_id) {
                                $query->where('users.id', $user_id);
                        });
                    })
                    ->get();
                    
        
        return $colaboradores = StatisticsService::getValuesForSales($colaboradores);
        
    }
}

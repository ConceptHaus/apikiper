<?php

namespace App\Http\Repositories\Statistics;

use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\User;
use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Http\Services\Funnel\FunnelService;
use App\Http\Services\UtilService;
use App\Http\Services\Statistics\StatisticsService;
use DB;
use Carbon\Carbon;

class StatisticsRep
{
    public static function ProspectosVsOportunidades($start_date, $end_date, $user_id=NULL)
    {
        $response = array();
        
        //Filter Days Prospectos
        $prospectos         = StatisticsRep::getProspectos($start_date, $end_date, $user_id, 'get');
       
        $prospectos_total   = StatisticsRep::getProspectos($start_date, $end_date, $user_id, 'count');

        //Filter Days Oportunidades
        $oportunidades = StatisticsRep::getOportunidades($start_date, $end_date, $user_id, 'get');
       
        //Oportunidades Cerradas
        $oportunidades_cerradas = StatisticsRep::getOportunidadesCerradas($start_date, $end_date, $user_id, 'count');

        //Oportunidades Cerradas by Fuente
        $oportunidades_by_fuente = StatisticsRep::getOportunidadesCerradasByFuente($start_date, $end_date, $user_id);

        $response['prospectos_filter_dates']        = StatisticsService::makeDatesRangeArray($prospectos, $start_date, $end_date);
        $response['oportunidades_filter_dates']     = StatisticsService::makeDatesRangeArray($oportunidades, $start_date, $end_date);
        $response['oportunidades_cerradas']         = $oportunidades_cerradas;
        $response['prospectos_total']               = $prospectos_total;
        $response['oportunidades_by_fuente']        = $oportunidades_by_fuente;
        $response['porcentaje_exito']               = ($prospectos_total > 0) ? number_format(($oportunidades_cerradas * 100) / $prospectos_total, 2) : 0;

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
        
    public static function FunnelOportunidades($start_date, $end_date, $user_id=NULL)
    {
        
        $oportunidades = array();
        $oportunidades['status'] =  CatStatusOportunidad::all();
        
        if(!empty($oportunidades['status'])){
            
            foreach ($oportunidades['status'] as $key => $status) {
                $oportunidades['status'][$key]['oportunidades'] = StatisticsRep::oportunidadesByStatus($status['id_cat_status_oportunidad'], $start_date, $end_date, $user_id);
            }
        }
        return $oportunidades;
    }

    public static function oportunidadesByStatus($status_id, $start_date, $end_date, $user_id=NULL)
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";

        $oportunidades = Oportunidad::join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->whereNull('oportunidades.deleted_at')
                        ->where('oportunidades.created_at',  '>=', $start_date)
                        ->where('oportunidades.created_at',  '<=', $end_date)
                        ->where('status_oportunidad.id_cat_status_oportunidad','=',$status_id);
        
        if(!is_null($user_id)){
            $oportunidades = $oportunidades->where('colaborador_oportunidad.id_colaborador', '=', $user_id);
        }

        $oportunidades = $oportunidades->count();

        return $oportunidades;

    }

    public static function monthlySalesHistory($start_date=NULL, $end_date=NULL, $user_id=NULL)
    {
        $hoy = Carbon::now()->toDateString();
        $first_day = date('Y-m-01');

        if ($first_day == $hoy) {
            $end_date = Carbon::now()->subDays(1)->toDateString();
        }

        if (is_null($end_date)) {
            $end_date = Carbon::now()->toDateString();;
        }
        
        return $ventas = Oportunidad::select(DB::raw('sum(detalle_oportunidad.valor * detalle_oportunidad.meses) as amount, DATE_FORMAT(oportunidades.created_at, "%m") as month, year(oportunidades.created_at) as year'))
                    ->join('detalle_oportunidad', 'detalle_oportunidad.id_oportunidad', '=', 'oportunidades.id_oportunidad')
                    ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', '=', 'oportunidades.id_oportunidad')
                    ->where(function ($query) use ($user_id) {
                        $query->when($user_id,  function ($query) use ($user_id) {
                                $query->where('colaborador_oportunidad.id_colaborador', $user_id);
                        });
                    })
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->when($start_date,  function ($query) use ($start_date, $end_date) {
                                $query->where('oportunidades.created_at', '>=', $start_date . ' 00:00:00');
                        });
                    })
                    ->where(function ($query) use ($end_date) {
                        $query->when($end_date,  function ($query) use ($end_date) {
                                $query->where('oportunidades.created_at', '<=', $end_date . ' 23:59:59');
                        });
                    })
                    ->groupby(DB::raw('Month(oportunidades.created_at)'))
                    ->orderby('oportunidades.created_at')
                    ->get();
                    
    }
    
    public static function ProspectosCerradosByColaborador($start_date, $end_date, $user_id=NULL)
    {
        $response               = array();
        $range_type             = UtilService::getDatesRangeForFilter($start_date, $end_date);
        $ranges                 = UtilService::getRangesFromRangeType($start_date, $end_date, $range_type);
        $oportunidades_cerradas = array();
        
        switch ($range_type) {
            case 'days':
                $oportunidades_cerradas[]   = [ 'start_date'            => $start_date,
                                                'end_date'              => $end_date,
                                                'prospectos_cerrados'   => StatisticsRep:: getOportunidadesCerradas($start_date, $end_date, $user_id, 'count'),
                                                'prospectos_totales'    => StatisticsRep:: getProspectos($start_date, $end_date, $user_id, 'count')];
                break;
            case 'weeks':
                foreach ($ranges as $key => $range) {
                    $oportunidades_cerradas[]   = [ 'start_date'            => $range['start_date'],
                                                    'end_date'              => $range['end_date'],
                                                    'prospectos_cerrados'   => StatisticsRep:: getOportunidadesCerradas($range['start_date'], $range['end_date'], $user_id, 'count'),
                                                    'prospectos_totales'    => StatisticsRep:: getProspectos($range['start_date'], $range['end_date'], $user_id, 'count')];
                }
                break;
            case 'months':
                foreach ($ranges as $key => $range) {
                    $oportunidades_cerradas[]   = [ 'start_date'            => $range['start_date'],
                                                    'end_date'              => $range['end_date'],
                                                    'prospectos_cerrados'   => StatisticsRep:: getOportunidadesCerradas($range['start_date'], $range['end_date'], $user_id, 'count'),
                                                    'prospectos_totales'    => StatisticsRep:: getProspectos($range['start_date'], $range['end_date'], $user_id, 'count')];
                }
                break;
            case 'years':
                foreach ($ranges as $key => $range) {
                    $oportunidades_cerradas[]   = [ 'start_date'            => $range['start_date'],
                                                    'end_date'              => $range['end_date'],
                                                    'prospectos_cerrados'   => StatisticsRep:: getOportunidadesCerradas($range['start_date'], $range['end_date'], $user_id, 'count'),
                                                    'prospectos_totales'    => StatisticsRep:: getProspectos($range['start_date'], $range['end_date'], $user_id, 'count')];
                }
                break;
            default:
                $oportunidades_cerradas[]   = [ 'start_date'            => $start_date,
                                                'end_date'              => $end_date,
                                                'prospectos_cerrados'   => StatisticsRep:: getOportunidadesCerradas($start_date, $end_date, $user_id, 'count'),
                                                'prospectos_totales'    => StatisticsRep:: getProspectos($start_date, $end_date, $user_id, 'count')];
                break;
        }
        return $oportunidades_cerradas;
    }

    public static function getProspectos($start_date, $end_date, $user_id, $action='get')
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";
       
        $prospectos =   Prospecto::select(DB::raw('DATE(prospectos.created_at) as date'), DB::raw('count(*) as total'))
                                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->where('prospectos.created_at', '>=', $start_date)
                                ->where('prospectos.created_at', '<=', $end_date);

        if(!is_null($user_id)){
            $prospectos = $prospectos->where('colaborador_prospecto.id_colaborador', $user_id);
        }

        if ($action == 'count') {
            $prospectos = $prospectos->count();
        }else{
            $prospectos = $prospectos->groupBy('date')->get();
        }

        return $prospectos;
    }

    public static function getOportunidades($start_date, $end_date, $user_id, $action='get')
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";

        $oportunidades   =   Oportunidad::select(DB::raw('DATE(oportunidades.created_at) as date'), DB::raw('count(*) as total'))
                                        ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')                            
                                        ->where('oportunidades.created_at', '>=', $start_date)
                                        ->where('oportunidades.created_at', '<=', $end_date);
        
        if(!is_null($user_id)){
            $oportunidades  =  $oportunidades->where('colaborador_oportunidad.id_colaborador', $user_id);
        }

        if ($action == 'count') {
            $oportunidades =  $oportunidades->count();
        }else{
            $oportunidades =  $oportunidades->groupBy('date')->get();
        }

        return $oportunidades;
    }

    public static function getOportunidadesCerradas($start_date, $end_date, $user_id, $action='get')
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";

        $oportunidades_cerradas =   Oportunidad::join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                                                ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')                            
                                                ->where('status_oportunidad.id_cat_status_oportunidad', 2)
                                                ->where('oportunidades.created_at', '>=', $start_date)
                                                ->where('oportunidades.created_at', '<=', $end_date);
        if(!is_null($user_id)){
            $oportunidades_cerradas  =  $oportunidades_cerradas->where('colaborador_oportunidad.id_colaborador', $user_id);
        }
        
        if ($action == 'count') {
            $oportunidades_cerradas =  $oportunidades_cerradas->count();
        }else{
            $oportunidades_cerradas =  $oportunidades_cerradas->get();
        }
        
        return $oportunidades_cerradas;
    }

    public static function getOportunidadesCerradasByFuente($start_date, $end_date, $user_id)
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";

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

        return $oportunidades_by_fuente;
    }

    public static function getProspectosTotal($start_date, $end_date, $user_id)
    {
        $prospectos_by_colaborador = array();

        if(is_null($user_id)){

            // $colaboradores = User::where('status', 1)->where('role_id', '<=', 3)->get();
            $colaboradores = User::where('status', 1)->get();
            
            if(count($colaboradores) > 0){
                foreach ($colaboradores as $key => $colaborador) {
                    $this_colaborador_prospectos                                = array();
                    $this_colaborador_prospectos['colaborador_id']              = $colaborador->id;
                    $this_colaborador_prospectos['nombre']                      = $colaborador->nombre;
                    $this_colaborador_prospectos['apellido']                    = $colaborador->apellido;
                    $this_colaborador_prospectos['prospectos_total']            = StatisticsRep::getProspectos($start_date, $end_date, $colaborador->id, 'count');
                    $this_colaborador_prospectos['prospectos_contactados']      = StatisticsRep::getProspectosContactados($start_date, $end_date, $colaborador->id, 'count');
                    $this_colaborador_prospectos['prospectos_no_contactados']   = $this_colaborador_prospectos['prospectos_total'] - $this_colaborador_prospectos['prospectos_contactados'];
                    if($this_colaborador_prospectos['prospectos_total'] > 0){
                        $prospectos_by_colaborador[]                            = $this_colaborador_prospectos;
                    }
                }
            }
        }
        else{
            $colaborador = User::find($user_id);
            if(isset($colaborador->id)){
                $this_colaborador_prospectos                                = array();
                $this_colaborador_prospectos['colaborador_id']              = $colaborador->id;
                $this_colaborador_prospectos['nombre']                      = $colaborador->nombre;
                $this_colaborador_prospectos['apellido']                    = $colaborador->apellido;
                $this_colaborador_prospectos['prospectos_total']            = StatisticsRep::getProspectos($start_date, $end_date, $colaborador->id, 'count');
                $this_colaborador_prospectos['prospectos_contactados']      = StatisticsRep::getProspectosContactados($start_date, $end_date, $colaborador->id, 'count');
                $this_colaborador_prospectos['prospectos_no_contactados']   = $this_colaborador_prospectos['prospectos_total'] - $this_colaborador_prospectos['prospectos_contactados'];
                $prospectos_by_colaborador[]                                = $this_colaborador_prospectos;
            }
            
        }
        
        return $prospectos_by_colaborador;
    }

    public static function getProspectosContactados($start_date, $end_date, $user_id, $action='get')
    {
        $start_date = $start_date ." 00:00:00";
        $end_date   = $end_date ." 23:59:59";
        
        $prospectos  =   Prospecto::select(DB::raw('DATE(prospectos.created_at) as date'), DB::raw('count(*) as total'))
                                    ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                    ->join('status_prospecto', 'status_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                    ->where('prospectos.created_at', '>=', $start_date)
                                    ->where('prospectos.created_at', '<=', $end_date)
                                    ->where('status_prospecto.id_cat_status_prospecto', 1);
        
        if(!is_null($user_id)){
            $prospectos = $prospectos->where('colaborador_prospecto.id_colaborador', $user_id);
        }

        if ($action == 'count') {
            $prospectos = $prospectos->count();
        }else{
            $prospectos = $prospectos->groupBy('date')->get();
        }

        return $prospectos;
    }
}

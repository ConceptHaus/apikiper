<?php

namespace App\Http\Services\Statistics;

use App\Http\Repositories\Statistics\StatisticsRep;
use App\Http\Services\UtilService;
use Carbon\Carbon;

class StatisticsService
{
    
    public static function ProspectosVsOportunidades($start_date, $end_date, $user_id)
    {
        return StatisticsRep::ProspectosVsOportunidades($start_date, $end_date, $user_id);
    }

    public static function makeDatesRangeArray($array, $start_date, $end_date)
    {
        $array_by_date = UtilService::arrayGroupByKey($array, 'date');

        return StatisticsService::generateEmptyDaysInfoForProspectosVsOportunidades($array_by_date, $start_date, $end_date);
    }


    public static function generateEmptyDaysInfoForProspectosVsOportunidades($array_by_date, $start_date, $end_date)
    {  
        $new_array_by_date = array();
        $start_d = strtotime($start_date); 
        $end_d   = strtotime($end_date); 

        while ($start_d <= $end_d) {
            
            if(isset($array_by_date[date('Y-m-d', $start_d)])){
                $new_array_by_date[date('Y-m-d', $start_d)] = ['date' => date('Y-m-d', $start_d), 'total' => $array_by_date[date('Y-m-d', $start_d)][0]['total']]; 
            }else{
                $new_array_by_date[date('Y-m-d', $start_d)] = ['date' => date('Y-m-d', $start_d), 'total' => 0];
            }
            
            $start_d = strtotime ( '+1 day' , $start_d ) ;
        }

        return $new_array_by_date;
    }

    public static function SalesHistoryByColaborador($start_date, $end_date, $user_id)
    {
        return StatisticsRep::SalesHistoryByColaborador($start_date, $end_date, $user_id);
    }

    public static function getValuesForSales($colaboradores)
    {
        
        $arrayColaboradores = array();
        $arrayVentas = array();
        $response = array();

        foreach ($colaboradores as $key => $value) {
            array_push($arrayColaboradores, $value["nombre_colaborador"]);
            array_push($arrayVentas, $value["ventas"]);
        }
        
        $response["Colaboradores"] = $arrayColaboradores;
        $response["Ventas"] = $arrayVentas;

        return $response;
    }

    public static function FunnelOportunidades($start_date, $end_date, $user_id)
    {
        return StatisticsRep::FunnelOportunidades($start_date, $end_date, $user_id);
    }
    
    public static function ProspectosCerradosByColaborador($start_date, $end_date, $user_id)
    {
        return StatisticsRep::ProspectosCerradosByColaborador($start_date, $end_date, $user_id);
    }

    public static function getProspectosTotal($start_date, $end_date, $user_id)
    {
        return StatisticsRep::getProspectosTotal($start_date, $end_date, $user_id);
    }

    public static function getProspectosByFuente($start_date, $end_date, $user_id)
    {
        return StatisticsRep::getProspectosByFuente($start_date, $end_date, $user_id);
    }
    
    public static function mostEffectiveProspects($start_date, $end_date){
        return StatisticsRep::mostEffectiveProspects($start_date, $end_date);

    }

    public static function campaignGenerateMoreProspects($start_date, $end_date, $id_campaign){
        return StatisticsRep::campaignGenerateMoreProspects($start_date, $end_date, $id_campaign);

    }

    public static function campaignGenerateMoreOpportunities($start_date, $end_date, $id_campaign, $id_origin){
        return StatisticsRep::campaignGenerateMoreOpportunities($start_date, $end_date, $id_campaign, $id_origin);

    }

    public static function campaignGeneratesMore($start_date, $end_date, $id_campaign, $id_origin){
        return StatisticsRep::campaignGeneratesMore($start_date, $end_date, $id_campaign, $id_origin);

    }

    public static function statusPossibleMoney($start_date, $end_date, $id_colaborador, $id_origin){
        return StatisticsRep::statusPossibleMoney($start_date, $end_date, $id_colaborador, $id_origin);

    }

    public static function getOneStatus($idStatus){
        return StatisticsRep::getOneStatus($idStatus);

    }

    public static function contactSpeed($start_date, $end_date){
        return StatisticsRep::contactSpeed($start_date, $end_date);

    }

    public static function getValuesForMostEffectiveProspects($values){
        $arrayNombre = array();
        $arrayValues = array();
        $arrayUrl = array();
        $response = array();

        foreach ($values as $key => $value) {
            array_push($arrayNombre, $value['nombre']);
            array_push($arrayValues, $value['count']);
            array_push($arrayUrl, $value['url']);
        }
        
        $response["Fuente"] = $arrayNombre;
        $response["Count"] = $arrayValues;
        $response["Url"] = $arrayUrl;

        return $response;
    }

    public static function getTotalSalesByColaborador($colaboradores)
    {
        $new_colaboradores = array();
        
        if(count($colaboradores) > 0){
            $colaboradores_by_sales = UtilService::arrayGroupByKey($colaboradores, 'nombre_colaborador');
           
            foreach ($colaboradores_by_sales as $key => $colaborador) {
                $total_by_colaborador   = 0;
                foreach ($colaborador as $i => $value) {
                    $total_by_colaborador = $total_by_colaborador + $value['ventas'];
                }   
                $new_colaboradores[] = ['nombre_colaborador' => $key, 'ventas' => $total_by_colaborador]; 
            }
        }
        
        return $new_colaboradores;
    }

    public static function getIncomePerOrigin($start_date, $end_date, $id_colaborador){
        $incomePerOrigin = StatisticsRep::getIncomePerOrigin($start_date, $end_date, $id_colaborador);

        if(!empty($incomePerOrigin)){
            foreach ($incomePerOrigin as $key => $value) {
                $valor_total = 0;
                foreach ($value as $op_key => $oportunidad) {
                    $valor_total =  $valor_total + $oportunidad['valor'];
                    $oportunidad['valor_formateado'] = number_format($oportunidad['valor'], 2);
                }
                $incomePerOrigin[$key]['total_ingresos']                =  number_format($valor_total, 2);
                $incomePerOrigin[$key]['total_oportunidades_cerradas']  = count($value);
                $incomePerOrigin[$key]['detalle_campanas']              = UtilService::arrayGroupByKey($value, 'integracion');
                foreach ($value as $op_key => $oportunidad) {
                    // $oportunidad['valor2'] = 6666666;
                    unset($incomePerOrigin[$key][$op_key]);
                }


                
                
                foreach ($incomePerOrigin[$key]['detalle_campanas'] as $op_key => $campana) {
                    $incomePerOrigin[$key]['detalle_campanas'][$op_key]['total_oportunidades_cerradas'] = count($campana);
                    $valor_total_por_campana = 0;
                    foreach ($campana as $op_key_2 => $op) {
                        $valor_total_por_campana = $valor_total_por_campana + $op['valor'];
                    }
                    $incomePerOrigin[$key]['detalle_campanas'][$op_key]['total_ingresos'] = $valor_total_por_campana;
                }

                
            }
            return $incomePerOrigin;
        }

        return $incomePerOrigin;
    }

}

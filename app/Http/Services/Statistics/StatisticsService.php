<?php

namespace App\Http\Services\Statistics;

use App\Http\Repositories\Statistics\StatisticsRep;
use App\Http\Services\UtilService;

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
        if(count($array_by_date) > 0){
            
            $new_array_by_date = array();
            $start_d = strtotime($start_date); 
            $end_d   = strtotime($end_date); 

            while ($start_d < $end_d) {
               
                if(isset($array_by_date[date('Y-m-d', $start_d)])){
                    $new_array_by_date[date('Y-m-d', $start_d)] = ['date' => date('Y-m-d', $start_d), 'total' => $array_by_date[date('Y-m-d', $start_d)][0]['total']]; 
                }else{
                    $new_array_by_date[date('Y-m-d', $start_d)] = ['date' => date('Y-m-d', $start_d), 'total' => 0];
                        
                }
                
                $start_d = strtotime ( '+1 day' , $start_d ) ;
            }

            return $new_array_by_date;
        }
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

    public static function getProspectosTotal($start_date, $end_date)
    {
        return StatisticsRep::getProspectosTotal($start_date, $end_date);
    }

}

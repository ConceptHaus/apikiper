<?php

namespace App\Http\Services\Statistics;

use Carbon\Carbon;

class MonthlySalesHistroyStatisticServiceHelper
{

    public static function adjustEndDate($end_date){
        
        if (date('Y-m-01') == Carbon::now()->toDateString()) {
            return $end_date = Carbon::now()->subDays(1)->toDateString();
        } else {
            return $end_date;
        }
    }

    public static function getMonthArrayPivot($start_date, $end_date){
        $initDateArray = explode("-", $start_date);
        $endDateArray = explode("-", $end_date);

        $initYear = $initDateArray[0];
        $initMonth = $initDateArray[1];
        $endYear = $endDateArray[0];
        $endMonth = $endDateArray[1];

        $monthArrayPivot = array();

        if($initYear == $endYear){
            $monthArrayPivot = MonthlySalesHistroyStatisticServiceHelper::processDateIntervalForPivotArray($initYear, $initMonth, $endMonth, $monthArrayPivot);
        }else{
            $monthArrayPivot = MonthlySalesHistroyStatisticServiceHelper::processDateIntervalForPivotArray($initYear, $initMonth, 12, $monthArrayPivot);
            $monthArrayPivot = MonthlySalesHistroyStatisticServiceHelper::processDateIntervalForPivotArray($endYear, 1, $endMonth, $monthArrayPivot);
        }

        return $monthArrayPivot;
    }

    private static function processDateIntervalForPivotArray($year, $initMonth, $endMonth, $pivotArray){
        $stringYear = strval( $year);
        for ($month=$initMonth; $month <= $endMonth; $month++) { 
            $stringMonth = $month < 10 ?  "0".$month : strval($month);
            array_push($pivotArray, ['month' => $stringMonth, 'year' => $stringYear, 'amount' => 0]);
        }
        return $pivotArray;
    }

    public static function fillArrayPivotWithData($pivotArray, $ventas){
        foreach ($pivotArray as $monthKey => $monthItem) {
            $month = $monthItem["month"];
            $year = $monthItem["year"];
            foreach ($ventas as $saleKey => $saleItem) {
                if ($saleItem['month'] == $month && $saleItem['year'] == $year) {
                    $pivotArray[$monthKey]["amount"] = intval($saleItem['amount']);
                    break;
                } 
            }
        }
        return $pivotArray;
    }

    public static function parseToStatisticsData($pivotArray){
        
        $arrayMonths = array();
        $arraySales = array();
        $response = array();

        foreach ($pivotArray as $key => $value) {
        
            $month = MonthlySalesHistroyStatisticServiceHelper::parseToMonthString($value["month"]);
            array_push($arrayMonths, $month."-".$value["year"]);
            array_push($arraySales, $value["amount"]);
        }
        
        $response["Mes"] = $arrayMonths;
        $response["Monto"] = $arraySales;

        return $response;
    }

    private static function parseToMonthString($month){
        switch ($month) {
            case "01": return "Enero";
            case "02": return "Febrero";
            case "03": return "Marzo";
            case "04": return "Abril";
            case "05": return "Mayo";
            case "06": return "Junio";
            case "07": return "Julio";
            case "08": return "Agosto";
            case "09": return "Septiembre";
            case "10": return "Octubre";
            case "11": return "Noviembre";
            case "12": return "Diciembre";
            default: return "";
        }
    }
}
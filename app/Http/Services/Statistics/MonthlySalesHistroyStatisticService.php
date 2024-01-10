<?php

namespace App\Http\Services\Statistics;

use App\Http\Repositories\Statistics\StatisticsRep;
use App\Http\Services\UtilService;
use App\Http\Services\Statistics\MonthlySalesHistroyStatisticServiceHelper;

class MonthlySalesHistroyStatisticService
{
    
    public static function monthlySalesHistory($start_date, $end_date, $user_id)
    {
        $end_date = MonthlySalesHistroyStatisticServiceHelper::adjustEndDate($end_date);
        $monthArrayPivot = MonthlySalesHistroyStatisticServiceHelper::getMonthArrayPivot($start_date, $end_date);
        $ventas = StatisticsRep::monthlySalesHistory($start_date, $end_date, $user_id);
        $monthArrayPivot = MonthlySalesHistroyStatisticServiceHelper::fillArrayPivotWithData($monthArrayPivot, $ventas);
        return MonthlySalesHistroyStatisticServiceHelper::parseToStatisticsData($monthArrayPivot);
    }

}
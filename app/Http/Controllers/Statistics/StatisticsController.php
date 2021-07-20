<?php

namespace App\Http\Controllers\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Statistics\StatisticsService;
use Auth;


class StatisticsController extends Controller
{

    public function ProspectosVsOportunidades(Request $request)
    {
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::ProspectosVsOportunidades($request->start_date, $request->end_date, $user_id);
    }

    public function SalesHistoryByColaborador(Request $request) {
        
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::SalesHistoryByColaborador($request->start_date, $request->end_date, $user_id);
    
    }
    
    public function FunnelOportunidades(Request $request)
    {
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::FunnelOportunidades($request->start_date, $request->end_date, $user_id);
    }

    public function monthlySalesHistory(Request $request){
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;

        return StatisticsService::monthlySalesHistory($request->start_date, $request->end_date, $user_id);
    }
    
    public function ProspectosCerradosByColaborador(Request $request)
    {
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::ProspectosCerradosByColaborador($request->start_date, $request->end_date, $user_id);
    }

    public function getProspectosTotal(Request $request)
    {
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::getProspectosTotal($request->start_date, $request->end_date, $user_id);
    }
}

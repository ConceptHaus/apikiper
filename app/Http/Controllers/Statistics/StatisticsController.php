<?php

namespace App\Http\Controllers\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Statistics\StatisticsService;
use App\Http\Services\Statistics\MonthlySalesHistroyStatisticService;
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

        return MonthlySalesHistroyStatisticService::monthlySalesHistory($request->start_date, $request->end_date, $user_id);
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

    public function getProspectosByFuente(Request $request)
    {
        $user_id = (is_null($request->user_id)) ? NULL : $request->user_id;
        
        return StatisticsService::getProspectosByFuente($request->start_date, $request->end_date, $user_id);
    }
    
    public function mostEffectiveProspects(Request $request){
        return StatisticsService::mostEffectiveProspects($request->start_date, $request->end_date);
    }

    public function campaignGenerateMoreProspects(Request $request){
        return StatisticsService::campaignGenerateMoreProspects($request->start_date, $request->end_date, $request->id_campaign);
    }

    public function campaignGenerateMoreOpportunities(Request $request){
        return StatisticsService::campaignGenerateMoreOpportunities($request->start_date, $request->end_date, $request->id_campaign, $request->id_origin);
    }
}

<?php
namespace App\Http\Services\Funnel;
use App\Http\Repositories\Funnel\FunnelRep;
use Illuminate\Support\Facades\Validator;

class FunnelService
{
    
    public static function getCatStatusOportunidades(){
        $cat_status_oportunidades = FunnelRep::getCatStatusOportunidades();
        return $cat_status_oportunidades;
    }
    
    public static function validator(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'color' => 'required',
            'funnel_visible' => 'required'
        ]);
    }

    public static function createFunnelStage($new_stage)
    {
        return FunnelRep::createFunnelStage($new_stage);
    }

    public static function deleteStatusOportunidad($id)
    {
        return FunnelRep::deleteStatusOportunidad($id);    
    }

    public static function updateStatusOportunidad($estatus)
    {
        return FunnelRep::updateStatusOportunidad($estatus);    
    }

    /*
    |  Funnel
    */

    // public static function getFunnelStages(){
    //     return FunnelRep::getFunnelStages();
    // }

    public static function getMisOportunidadesByFunnelStage($colaborador_id){
        return FunnelRep::getMisOportunidadesByFunnelStage($colaborador_id);
    }

    public static function updateOportunidadStatus($oportunidad_id, $new_status){
        return FunnelRep::updateOportunidadStatus($oportunidad_id, $new_status);
    }

    public static function getColaboradoresWithOportunidades(){
        return FunnelRep::getColaboradoresWithOportunidades();
    }

    public static function getOportunidadesByFunnelStage()
    {
        return FunnelRep::getOportunidadesByFunnelStage();
    }
}

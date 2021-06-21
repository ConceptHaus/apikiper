<?php
namespace App\Http\Services\Funnel;
use App\Http\Repositories\Funnel\FunnelRep;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\Settings\SettingsService;

class FunnelService
{
    
    public static function getCatStatusOportunidades(){
        $cat_status_oportunidades = FunnelRep::getCatStatusOportunidades();
        return $cat_status_oportunidades;
    }
    
    public static function validator(array $data)
    {
        return Validator::make($data, [
            'status' => 'required|string|max:20|unique:cat_status_oportunidad,status,NULL,id_cat_status_oportunidad,deleted_at,NULL',
            'color' => 'required',
            'funnel_visible' => 'required'
        ]);
    }

    public static function validatorUpdate(array $data)
    {

        return Validator::make($data, [
            'status' => 'required|string|max:20|unique:cat_status_oportunidad,status,'.$data['id_cat_status_oportunidad'].',id_cat_status_oportunidad,deleted_at,NULL',
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

    public static function getMaxEstatusOportunidadMaxCount()
    {
        return SettingsService::getMaxEstatusOportunidadMaxCount();
    }

    public static function updateStatusOportunidadVisibles($status_oportunidad_visibles)
    {
        return FunnelRep::updateStatusOportunidadVisibles($status_oportunidad_visibles);
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

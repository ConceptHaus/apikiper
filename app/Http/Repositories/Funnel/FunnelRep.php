<?php

namespace App\Http\Repositories\Funnel;

use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Modelos\Oportunidad\StatusOportunidad;
use DB;

class FunnelRep
{
    public static function getCatStatusOportunidades()
    {
        return CatStatusOportunidad::all();
    }

    public static function createFunnelStage($new_cat_status_oportunidad)
    {
        try{
            DB::beginTransaction();
            
            $cat_status_oportunidad                 = new CatStatusOportunidad;
            $cat_status_oportunidad->status         = $new_cat_status_oportunidad['nombre'];
            $cat_status_oportunidad->color          = $new_cat_status_oportunidad['color'];
            $cat_status_oportunidad->funnel_visible = ($new_cat_status_oportunidad['funnel_visible']) ? 1 : 0;
            $cat_status_oportunidad->save();
            
            DB::commit();
            
            $response   = array('message'   => 'Registro Correcto',
                                'error'     => false,
                                'data'      =>'');

        }catch(Excpetion $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo agregar un colaborador"));
            
            $response   = array('message'   => $e,
                                'error'     => true);
        }

        return $response;
    }

    public static function  deleteStatusOportunidad($id)
    {
        
        try{
            DB::beginTransaction();
            
            CatStatusOportunidad::where('id_cat_status_oportunidad', $id)->delete();
            
            DB::commit();
            
            $response   = array('message'   => 'Status oportunidad eliminado de manera correcta',
                                'error'     => false,
                                'data'      =>'');
        }
        catch(Excpetion $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo eliminar el status de oportunidad"));
            
            $response   = array('message'   => $e,
                                'error'     => true);
        }

        return $response;
    }

    public static function  updateStatusOportunidad($estatus)
    {
        
        try{
            DB::beginTransaction();
            
            $cat_status_oportunidad                 = CatStatusOportunidad::find($estatus['id_cat_status_oportunidad']);
            $cat_status_oportunidad->status         = $estatus['status'];
            $cat_status_oportunidad->funnel_visible = $estatus['funnel_visible'];
            $cat_status_oportunidad->color          = $estatus['color'];
            $cat_status_oportunidad->save();
            
            DB::commit();
            
            $response   = array('message'   => 'Status oportunidad actualizado de manera correcta',
                                'error'     => false,
                                'data'      =>'');
        }
        catch(Excpetion $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo actualizar el status de oportunidad"));
            
            $response   = array('message'   => $e,
                                'error'     => true);
        }

        return $response;
    }

    /*
    |  Funnel
    */

    public static function getFunnelStages()
    {
        return CatStatusOportunidad::where('funnel_visible',1)->get();
    }

    public static function getMisOportunidadesByFunnelStage($colaborador_id)
    {
        $oportunidades = array();
        $oportunidades['funnel_stages'] =   CatStatusOportunidad::where('funnel_visible',1)
                                                                ->orderBy('funnel_order', 'asc')
                                                                ->get();
        
        if(!empty($oportunidades['funnel_stages'])){
            foreach ($oportunidades['funnel_stages'] as $key => $funnel_stage) {
                $oportunidades['funnel_stages'][$key]['oportunidades'] = FunnelRep::oportunidadesPorColaboradorPorStatus($colaborador_id, $funnel_stage['id_cat_status_oportunidad']);
            }
        }
        return $oportunidades;
    }

    public static function oportunidadesPorColaboradorPorStatus($colaborador_id, $status_id)
    {
        $oportunidades = DB::table('oportunidades')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->whereNull('oportunidades.deleted_at')
                        ->where('colaborador_oportunidad.id_colaborador','=',$colaborador_id)
                        ->where('status_oportunidad.id_cat_status_oportunidad','=',$status_id)->get();
        if(!empty($oportunidades)){
            //Drag & Drop Properties for plugin
            foreach($oportunidades as $key => $oportunidad){
                $oportunidades[$key]->effectAllowed = "move";
                $oportunidades[$key]->disable = false;
                $oportunidades[$key]->valor = "$ ".number_format($oportunidades[$key]->valor, 2);
            }
        }

        return $oportunidades;
    }

    public static function  updateOportunidadStatus($oportunidad_id, $new_status)
    {
        
        try{
            DB::beginTransaction();
            
            $oportunidad                            = StatusOportunidad::where('id_oportunidad', $oportunidad_id)->first();
            $oportunidad->id_cat_status_oportunidad = $new_status;
            $oportunidad->save();
            
            DB::commit();
            
            $response   = array('message'   => 'Oportunidad actualizada de manera correcta',
                                'error'     => false,
                                'data'      =>'');
        }
        catch(Excpetion $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo actualizar la oportunidad"));
            
            $response   = array('message'   => $e,
                                'error'     => true);
        }

        return $response;
    }

}

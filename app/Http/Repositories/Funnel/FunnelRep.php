<?php

namespace App\Http\Repositories\Funnel;

use App\Modelos\Oportunidad\CatStatusOportunidad;
use DB;

class FunnelRep
{
    public static function getFunnelStages(){
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

}

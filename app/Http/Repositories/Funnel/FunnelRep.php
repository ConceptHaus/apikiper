<?php

namespace App\Http\Repositories\Funnel;

use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Http\Services\Funnel\FunnelService;
use DB;
use Log;

class FunnelRep
{
    public static function getCatStatusOportunidades()
    {
        return CatStatusOportunidad::orderBy('funnel_visible', 'ASC')->orderBy('funnel_order', 'ASC')->get();
    }

    public static function createFunnelStage($new_cat_status_oportunidad)
    {
        $existing_cat_status_oportunidad = CatStatusOportunidad::all();
        $max_count = FunnelService::getMaxEstatusOportunidadMaxCount();
        if (count($existing_cat_status_oportunidad) >= $max_count) {
            $response   = array('message'   => ['No fue posible crear registro. El límite de estatus oportunidades es '.$max_count],
                                'error'     => true,
                                'data'      =>'');
            return $response;
        }
    
        try{
            DB::beginTransaction();

            $new_position_in_funnel = NULL;

            if($new_cat_status_oportunidad['funnel_visible'] == 1){
                $last_item_order = CatStatusOportunidad::where("funnel_visible", 1)->where("deletable", 1)->orderBy("funnel_order", "DESC")->first();
                $last_non_deletable_item = CatStatusOportunidad::where("funnel_visible", 1)->where("deletable", 0)->orderBy("funnel_order", "DESC")->first();  
                if(isset($last_item_order->funnel_order)){
                    if(($last_item_order->funnel_order + 1) == $last_non_deletable_item->funnel_order){
                        $last_non_deletable_item->funnel_order = $last_item_order->funnel_order + 2;
                        $last_non_deletable_item->save();
                    }
                    $new_position_in_funnel = $last_item_order->funnel_order + 1;
                }else{
                    $new_position_in_funnel = 1;    
                }
            }

            $cat_status_oportunidad                 = new CatStatusOportunidad;
            $cat_status_oportunidad->status         = $new_cat_status_oportunidad['status'];
            $cat_status_oportunidad->color          = $new_cat_status_oportunidad['color'];
            $cat_status_oportunidad->funnel_visible = ($new_cat_status_oportunidad['funnel_visible']) ? 1 : 0;
            $cat_status_oportunidad->funnel_order   = $new_position_in_funnel;
            $cat_status_oportunidad->save();
            
            DB::commit();
            
            $response   = array('message'   => 'Registro Correcto',
                                'error'     => false,
                                'data'      => ['last_item_order->funnel_order' => $last_item_order->funnel_order, 'last_non_deletable_item' => $last_non_deletable_item]);

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
        $oportunidades = FunnelRep::oportunidadesPorStatus($id);
        // print_r($oportunidades);
        $status = CatStatusOportunidad::find($id);

        if (isset($status->deletable) AND $status->deletable == 0) {
            $response   = array('message'   => 'No se pudo eliminar el estatus de oportunidad, este estatus no es elimibale del sistema.',
                                'error'     => true,
                                'data'      =>'');
        }
        elseif(count($oportunidades) > 0){
            $response   = array('message'   => 'No se pudo eliminar el estatus de oportunidad porque tiene oportunidades activas.',
                                'error'     => true,
                                'data'      =>'');
        }
        else{
            try{
                DB::beginTransaction();

                if($status->funnel_visible == 1){
                    $old_position_in_funnel = $status->funnel_order;
                    $last_items =   CatStatusOportunidad::where("funnel_visible", 1)
                                                        ->where("funnel_order", ">", $old_position_in_funnel)
                                                        ->orderBy("funnel_order", "ASC")
                                                        ->get();  
                    
                    if(count($last_items) > 0){
                        foreach ($last_items as $key => $last_item) {
                            if($last_item->id_cat_status_oportunidad != 1 && $last_item->id_cat_status_oportunidad != 2){
                                $status_to_be_updated = CatStatusOportunidad::find($last_item->id_cat_status_oportunidad);
                                $status_to_be_updated->funnel_order = $status_to_be_updated->funnel_order - 1;
                                $status_to_be_updated->save();
                            }
                        }
                    }    
                }
                
                CatStatusOportunidad::where('id_cat_status_oportunidad', $id)->delete();
                
                DB::commit();
                
                $response   = array('message'   => 'Se eliminó el estatus de oportunidad con éxito',
                                    'error'     => false,
                                    'data'      =>'');
            }
            catch(Excpetion $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo eliminar el estatus de oportunidad"));
                
                $response   = array('message'   => $e,
                                    'error'     => true);
            }
        }
        return $response;
    }

    public static function  updateStatusOportunidad($estatus)
    {
        
        try{
            DB::beginTransaction();
            
            $cat_status_oportunidad = CatStatusOportunidad::find($estatus['id_cat_status_oportunidad']);
            $new_position_in_funnel = 0;
            $old_position_in_funnel = 0;
            
            if($cat_status_oportunidad->funnel_visible == 0 && $estatus['funnel_visible'] == 1){
                $last_item_order = CatStatusOportunidad::where("funnel_visible", 1)->where("deletable", 1)->orderBy("funnel_order", "DESC")->first();  
                if(isset($last_item_order->funnel_order)){
                    $new_position_in_funnel = $last_item_order->funnel_order + 1;
                }else{
                    $new_position_in_funnel = 2;
                }   
            }

            if($cat_status_oportunidad->funnel_visible == 1 && $estatus['funnel_visible'] == 0){
                $old_position_in_funnel = $cat_status_oportunidad->funnel_order;
                $last_items =   CatStatusOportunidad::where("funnel_visible", 1)
                                                    ->where("funnel_order", ">", $old_position_in_funnel)
                                                    ->orderBy("funnel_order", "ASC")
                                                    ->get();  
                
                if(count($last_items) > 0){
                    foreach ($last_items as $key => $last_item) {
                        if($last_item->id_cat_status_oportunidad != 1 && $last_item->id_cat_status_oportunidad != 2){
                            $status_to_be_updated = CatStatusOportunidad::find($last_item->id_cat_status_oportunidad);
                            $status_to_be_updated->funnel_order = $status_to_be_updated->funnel_order - 1;
                            $status_to_be_updated->save();
                        }
                    }
                }    
            }

            if($cat_status_oportunidad->funnel_visible == 1 && $estatus['funnel_visible'] == 1){
                $new_position_in_funnel = $cat_status_oportunidad->funnel_order;    
            }
            
            $cat_status_oportunidad->status         = $estatus['status'];
            $cat_status_oportunidad->funnel_visible = $estatus['funnel_visible'];
            $cat_status_oportunidad->color          = $estatus['color'];
            $cat_status_oportunidad->funnel_order   = ($estatus['funnel_visible']) ? $new_position_in_funnel : NULL;
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

    public static function  updateStatusOportunidadVisibles($status_oportunidad_visibles)
    {
         
        if(count($status_oportunidad_visibles) > 0){
            foreach ($status_oportunidad_visibles as $key => $status_oportunidad_visible) {
                $status_to_be_updated = CatStatusOportunidad::find($status_oportunidad_visible['id_cat_status_oportunidad']);
                $status_to_be_updated->funnel_order = $status_oportunidad_visible['funnel_order'];
                $status_to_be_updated->save();
            }
        }   
          
        $response   = array(
            // 'message'   => 'Status oportunidad actualizado de manera correcta',
            //                 'error'     => false,
                            'data'      => $status_oportunidad_visible['id_cat_status_oportunidad']);
        

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
        // print_r($colaborador_id);
        $oportunidades = array();
        $oportunidades['funnel_stages'] =   CatStatusOportunidad::where('funnel_visible',1)
                                                                ->orderBy('funnel_order', 'asc')
                                                                ->get();
        
        if(!empty($oportunidades['funnel_stages'])){
            foreach ($oportunidades['funnel_stages'] as $key => $funnel_stage) {
                $oportunidades['funnel_stages'][$key]['oportunidades']          = FunnelRep::oportunidadesPorColaboradorPorStatus($colaborador_id, $funnel_stage['id_cat_status_oportunidad']);
                $oportunidades['funnel_stages'][$key]['total_oportunidades']    = FunnelRep::getTotalCountOportunidades($oportunidades['funnel_stages'][$key]['oportunidades']);
                $oportunidades['funnel_stages'][$key]['total_valor']            = FunnelRep::getTotalValueOportunidades($oportunidades['funnel_stages'][$key]['oportunidades']);
            }
        }
        return $oportunidades;
    }

    public static function oportunidadesPorColaboradorPorStatus($colaborador_id, $status_id)
    {
        $oportunidades = DB::table('oportunidades')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->whereNull('oportunidades.deleted_at')
                        ->where('colaborador_oportunidad.id_colaborador','=',$colaborador_id)
                        ->where('status_oportunidad.id_cat_status_oportunidad','=',$status_id)
                        ->get();
        if(!empty($oportunidades)){
            //Drag & Drop Properties for plugin
            foreach($oportunidades as $key => $oportunidad){
                $oportunidades[$key]->effectAllowed = "move";
                $oportunidades[$key]->disable = false;
                $oportunidades[$key]->value = "$ ".number_format($oportunidades[$key]->valor * $oportunidades[$key]->meses, 2);
                $oportunidades[$key]->valor = $oportunidades[$key]->valor;
            }
        }

        return $oportunidades;
    }

    public static function oportunidadesPorStatus($status_id)
    {
        $oportunidades = DB::table('oportunidades')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->whereNull('oportunidades.deleted_at')
                        ->where('status_oportunidad.id_cat_status_oportunidad','=',$status_id)
                        ->get();
        if(!empty($oportunidades)){
            //Drag & Drop Properties for plugin
            foreach($oportunidades as $key => $oportunidad){

                if( $oportunidad->id_oportunidad ){

                    $contactoOportunidad = DB::table('oportunidad_prospecto as op')
                    ->join('prospectos as p', 'p.id_prospecto', 'op.id_prospecto')
                    ->join('medio_contacto_prospectos as mcp', 'mcp.id_prospecto', 'p.id_prospecto')
                    ->where('op.id_oportunidad', '=', $oportunidad->id_oportunidad)
                    ->orderBy('mcp.fecha', 'desc')
                    ->orderBy('mcp.hora', 'desc')
                    ->first();

                    $notificationDetail = [];
                    $notificationDetail['text']  = 'Ultimo Seguimiento: 1 día.';
                    $notificationDetail['color'] = 'black';
                    
                    if ($contactoOportunidad) {
                        $date1 = date_create($contactoOportunidad->fecha ? $contactoOportunidad->fecha : date("Y-m-d H:i:s") );
                        $date2 = date_create(date("Y-m-d H:i:s"));
                        $dateDiff = date_diff($date1, $date2);
                        $days = (int)$dateDiff->format("%a");

                        if ($days == 1) {
                            $notificationDetail['text']  = 'Ultimo Seguimiento: 1 día.';
                            $notificationDetail['color'] = 'black';
                        }

                        if ($days >= 2 && $days <= 5) {
                            $notificationDetail['text']  = 'Ultimo Seguimiento: '.$days.' días.';
                            $notificationDetail['color'] = 'orange';
                        }

                        if ($days >= 6) {
                            $notificationDetail['text']  = 'Ultimo Seguimiento: '.$days.' días.';
                            $notificationDetail['color'] = 'red';
                        }

                    }

                    $oportunidad->notificationDetail = $notificationDetail;
                }

                $oportunidades[$key]->effectAllowed = "move";
                $oportunidades[$key]->disable = false;
                $oportunidades[$key]->value = "$ ".number_format($oportunidades[$key]->valor * $oportunidades[$key]->meses, 2);
                $oportunidades[$key]->valor = $oportunidades[$key]->valor;
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

    public static function getColaboradoresWithOportunidades()
    {
        $colaboradores = DB::table('users')
                        ->select('users.nombre', 'users.apellido', 'users.id')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('oportunidades','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                        ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                        ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                        ->whereNull('oportunidades.deleted_at')
                        ->whereNUll('detalle_oportunidad.deleted_at')
                        ->whereNull('oportunidad_prospecto.deleted_at')
                        ->whereNull('colaborador_oportunidad.deleted_at')
                        ->whereNull('users.deleted_at')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->whereNull('servicio_oportunidad.deleted_at')
                        ->orderBy('users.nombre', 'asc')
                        ->groupBy('users.id')
                        ->get();

        return $colaboradores;
    }

    public static function getOportunidadesByFunnelStage()
    {
        $oportunidades = array();
        $oportunidades['funnel_stages'] =   CatStatusOportunidad::where('funnel_visible',1)
                                                                ->orderBy('funnel_order', 'asc')
                                                                ->get();
        
        if(!empty($oportunidades['funnel_stages'])){
            foreach ($oportunidades['funnel_stages'] as $key => $funnel_stage) {
                $oportunidades['funnel_stages'][$key]['oportunidades']          = FunnelRep::oportunidadesPorStatus($funnel_stage['id_cat_status_oportunidad']);
                $oportunidades['funnel_stages'][$key]['total_oportunidades']    = FunnelRep::getTotalCountOportunidades($oportunidades['funnel_stages'][$key]['oportunidades']);
                $oportunidades['funnel_stages'][$key]['total_valor']            = FunnelRep::getTotalValueOportunidades($oportunidades['funnel_stages'][$key]['oportunidades']);
            }
        }
        return $oportunidades;
    }

    public static function getTotalValueOportunidades($oportunidades)
    {
        $total = 0;
        if(!empty($oportunidades)){
            foreach ($oportunidades as $key => $oportunidad) {
               $total = $total + ($oportunidad->valor * $oportunidad->meses);
            }    
        }
        if($total > 0){
            return "$ ".number_format($total, 2);
        }else{
            return "";
        }
        
    }

    public static function getTotalCountOportunidadesItemsString($oportunidades)
    {
        $total_count    = "";
        $count          = count($oportunidades);

        if($count > 0){
            $items       = ($count == 1) ? "item" : "items" ;
            $total_count = " | " . $count . " " . $items;
        }

        return $total_count;
    }

    public static function getTotalCountOportunidades($oportunidades)
    {
        return count($oportunidades);
    }

}

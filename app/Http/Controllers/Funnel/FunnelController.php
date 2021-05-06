<?php

namespace App\Http\Controllers\Funnel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

use App\Http\Services\Funnel\FunnelService;
use DB;
use Auth;

class FunnelController extends Controller
{
    
    public function getCatStatusOportunidades(Request $request)
    {    
        $cat_status_oportunidades = FunnelService::getCatStatusOportunidades();
        return response()->json([
            'error' => false,
            'data'  => $cat_status_oportunidades,
        ],200);
    }

    public function createFunnelStage(Request $request)
    {
        $validator = FunnelService::validator($request->all());
        // print_r($request->input()); return [];
        if($validator->passes()){
            $new_stage = array( 'nombre'         => $request->nombre,
                                'color'          => $request->color,
                                'funnel_visible' => $request->funnel_visible);

            $stage = FunnelService::createFunnelStage($new_stage);

            if(!$stage['error']){
                
                return response()->json([
                            'message'   => $stage['message'],
                            'error'     => $stage['error'],
                            'data'      => $stage['data']
                        ],200);
            }else{
                return response()->json([
                            'message'   => $stage['message'],
                            'error'     => $stage['error'],
                        ],400);    
            }
        }
        $errores = $validator->errors()->toArray();

        return response()->json([
            'error'=>true,
            'messages'=> $errores
        ],400);
    }

    public function deleteStatus(Request $request, $id)
    {
        $delete_status_oportunidad = FunnelService::deleteStatusOportunidad($id);

        if(!$delete_status_oportunidad['error']){
            
            return response()->json([
                        'message'   => $delete_status_oportunidad['message'],
                        'error'     => $delete_status_oportunidad['error'],
                        'data'      => $delete_status_oportunidad['data']
                    ],200);
        }else{
            return response()->json([
                        'message'   => $delete_status_oportunidad['message'],
                        'error'     => $delete_status_oportunidad['error'],
                    ],400);    
        }  
    }

    public function updateStatus(Request $request)
    {
        // print_r($request->input()); return [];
        $estatus    = array('id_cat_status_oportunidad' => $request->id_cat_status_oportunidad,
                            'status'                    => $request->status,
                            'funnel_visible'            => $request->funnel_visible,
                            'color'                     => $request->color);
       
        $update_status_oportunidad = FunnelService::updateStatusOportunidad($estatus);

        if(!$update_status_oportunidad['error']){
            
            return response()->json([
                        'message'   => $update_status_oportunidad['message'],
                        'error'     => $update_status_oportunidad['error'],
                        'data'      => $update_status_oportunidad['data']
                    ],200);
        }else{
            return response()->json([
                        'message'   => $update_status_oportunidad['message'],
                        'error'     => $update_status_oportunidad['error'],
                    ],400);    
        }  
    }

    /*
    |  Funnel
    */

    public function getFunnelStages(Request $request)
    {    
        $funnel_stages = FunnelService::getFunnelStages();
        return response()->json([
            'error'=>false,
            'data'=>$funnel_stages,
        ],200);
    }
    
    public function getMisOportunidades(Request $request)
    {    
        $colaborador_id = Auth::user()->id;
        $funnel_stages = FunnelService::getMisOportunidadesByFunnelStage($colaborador_id);
        return response()->json([
            'error'=>false,
            'data'=>$funnel_stages,
        ],200);
    }

    public function updateOportunidadStatus(Request $request)
    {    
        // print_r($request->input());
        $oportunidad = FunnelService::updateOportunidadStatus($request->id_oportunidad, $request->target_status);
        return response()->json([
            'error'=>false,
            'data'=>$oportunidad,
        ],200);
    }
   
}

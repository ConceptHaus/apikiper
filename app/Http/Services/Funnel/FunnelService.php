<?php
namespace App\Http\Services\Funnel;
use App\Http\Repositories\Funnel\FunnelRep;
use Illuminate\Support\Facades\Validator;

class FunnelService
{
    public static function getFunnelStages(){
        $funnel_stages = FunnelRep::getFunnelStages();
        return $funnel_stages;
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
}

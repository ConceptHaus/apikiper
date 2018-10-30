<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;
use DB;
use Mail;


class UserController extends Controller
{
   
    

    public function getAuthUser(Request $request){
        $id_user = $this->guard()->user()->id;
        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $recordatorios = DB::table('recordatorios_prospecto')
                        ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                        ->where('recordatorios_prospecto.id_colaborador',$id_user)->get();
        $detalle = DetalleColaborador::where('id_colaborador',$this->guard()->user()->id)->first();

        return response()->json([
            'user'=>$this->guard()->user(),
            'detalle'=>$detalle,
            'oportunidades'=>$oportunidades,
            'recordatorio'=>$recordatorios
        ],200);
    
    }

       /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}

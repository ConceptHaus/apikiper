<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;
use App\Modelos\Colaborador\FotoColaborador;
use DB;
use Mail;


class UserController extends Controller
{
    protected function validatorUpdate(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            'correo' => 'required|email|max:255'
        ]);
    }


    public function getAuthUser(Request $request){
        $id_user = $this->guard()->user()->id;
        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $status_1 = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $recordatorios = DB::table('recordatorios_prospecto')
                        ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                        ->where('recordatorios_prospecto.id_colaborador',$id_user)->get();
        $detalle = DetalleColaborador::where('id_colaborador',$this->guard()->user()->id)
                        ->first();
        $img = FotoColaborador::where('id_colaborador', $this->guard()->user()->id)
                        ->select('url_foto')
                        ->first();

        return response()->json([
            'user'=>$this->guard()->user(),
            'detalle'=>$detalle,
            'img_perfil'=>$img,
            'oportunidades'=>[
                'status_1'=>'',
                'status_2'=>'',
                'status_3'=>''
            ],
            'recordatorios'=>$recordatorios
        ],200);

    }

    public function updateME(Request $request){
        $id_me = $this->guard()->user()->id;
        $me = User::where('id',$id_me)->first();
        $me_ext = DetalleColaborador::where('id_colaborador',$id_me)->first();
        $validator = $this->validatorUpdate($request->all());
        if($validator->passes()){
            try{
            DB::beginTransaction();
            $me->nombre = $request->nombre;
            $me->apellido = $request->apellido;
            $me->email = $request->correo;
            $me_ext->puesto = $request->puesto;
            $me_ext->telefono = $request->telefono;
            // $me_ext->fecha_nacimiento = $request->fecha_nacimiento;
            $me->save();
            $me->detalle()->save($me_ext);
            $meRes = User::GetOneUser($id_me);
            DB::commit();
            return response()->json([
                'message'=>'Success',
                'error'=>false,
                'data'=>$meRes
                ]);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'message'=>$e,
                    'error'=>true
                ],400);
            }
        }

        $errores = $validator->errors()->toArray();

            return response()->json([
                'error'=>true,
                'messages'=> $errores
            ],400);

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

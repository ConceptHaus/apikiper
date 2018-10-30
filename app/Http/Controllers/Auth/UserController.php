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
        $detalle = DetalleColaborador::where('id_colaborador',$this->guard()->user()->id)->first();
        return response()->json([
            'user'=>$this->guard()->user(),
            'detalle'=>$detalle
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

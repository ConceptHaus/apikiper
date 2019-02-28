<?php

namespace App\Http\Controllers\Empresas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use App\Modelos\Empresa\Empresa;

use DB;

class EmpresaController extends Controller
{
    public function registerCompany(Request $request){
        
        try{
            DB::beginTransaction();

            $empresa = new Empresa;
            if(isset($request->cp))
                $empresa->cp = $request->cp;
            if(isset($request->calle))
                $empresa->calle = $request->calle;
            if(isset($request->colonia))
                $empresa->colonia = $request->colonia;
            if(isset($request->num_ext))
                $empresa->num_ext = $request->num_ext;
            if(isset($request->num_int))
                $empresa->num_int = $request->num_int;
            if(isset($request->pais))
                $empresa->pais = $request->pais;
            if(isset($request->estado))
                $empresa->estado = $request->estado;
            if(isset($request->municipio))
                $empresa->municipio = $request->municipio;
            if(isset($request->ciudad))
                $empresa->ciudad = $request->ciudad;
            if(isset($request->telefono))
                $empresa->telefono = $request->telefono;
            if(isset($request->num_empleados))
                $empresa->num_empleados = $request->num_empleados;
            if(isset($request->industria))
                $empresa->id_cat_industria = $request->industria;
            if(isset($request->web))
                $empresa->web = $request->web;
            if(isset($request->rfc))
                $empresa->rfc = $request->rfc;
            if(isset($request->razon_social))
                $empresa->razon_social = $request->razon_social;
            $empresa->nombre = $request->empresa;
            $empresa->save();
            DB::commit();
                
            return response()->json([
                    'message'=>'Registro Correcto',
                    'error'=>false,
                    'data'=>$empresa,
                ],200);
        }catch(Exception $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo crear la empresa, revísame :("));
            return response()->json([
                'message'=>$e,
                'error'=>true,
            ],400);
        }
        
    }
}

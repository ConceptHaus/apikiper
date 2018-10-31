<?php
namespace App\Http\Controllers\Colaboradores;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;

use DB;
use Mail;

class ColaboradoresController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'apellido'=> 'required|string|max:255',
            'is_admin'=> 'required|boolean|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            'fecha_nacimiento'=> 'required|string|max:255'
        ]);
    }
    protected function validatorUpdate(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'is_admin'=> 'required|boolean|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            'fecha_nacimiento'=> 'required|string|max:255'
        ]);
    }
    public function registerColaborador(Request $request){
            $validator = $this->validator($request->all());

            if($validator->passes()){
                 try{

                    DB::beginTransaction();
                    $colaborador = new User;
                    $colaborador->nombre = $request->nombre;
                    $colaborador->apellido = $request->apellido;
                    $colaborador->email = $request->email;
                    $pass = str_random(8);
                    $colaborador->password = bcrypt($pass);
                    $colaborador->is_admin = $request->is_admin;
                    $colaborador->status = 1;
                    $colaborador_ext = new DetalleColaborador;
                    $colaborador_ext->puesto = $request->puesto;
                    $colaborador_ext->telefono = $request->telefono;
                    $colaborador_ext->fecha_nacimiento = $request->fecha_nacimiento;
                    $colaborador->save();
                    $colaborador->detalle()->save($colaborador_ext);
                    $arrayColaborador = $colaborador->toArray();
                    $arrayColaborador['pass'] = $pass;
                    
                    Mail::send('auth.emails.register',$arrayColaborador, function($contacto) use ($arrayColaborador){
                        $contacto->from('contacto@kiper.app', 'Kiper');
                        $contacto->to($arrayColaborador['email'], 'Termina tu registro en Kiper');
                    });
                    DB::commit();
                    return response()->json([
                        'message'=>'Successfully registered',
                        'error'=>false,
                        'data'=> $this->transformColaboradorToJson($colaborador,$colaborador_ext)
                    ],200);

                }catch(Excpetion $e){
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

    public function getAllColaboradores(){
        $colaboradores = User::GetallUsers();
        
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>$colaboradores
        ],200);
    }

    public function getOneColaborador($id){
        $colaborador = User::GetOneUser($id);

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>$colaborador
        ]);
    }

    public function updateColaborador(Request $request){
        $id_colaborador = $request->id;
        $colaborador = User::where('id',$id_colaborador)->first();
        $colaborador_ext = DetalleColaborador::where('id_colaborador',$id_colaborador)->first();
        $validator = $this->validatorUpdate($request->all());
        if($validator->passes()){
            try{
            DB::beginTransaction();
            $colaborador->nombre = $request->nombre;
            $colaborador->apellido = $request->apellido;
            $colaborador_ext->puesto = $request->puesto;
            $colaborador_ext->telefono = $request->telefono;
            $colaborador_ext->fecha_nacimiento = $request->fecha_nacimiento;
            $colaborador->save();
            $colaborador->detalle()->save($colaborador_ext);
            $colaboradorRes = User::GetOneUser($id_colaborador);
            DB::commit();
            return response()->json([
                'message'=>'Success',
                'error'=>false,
                'data'=>$colaboradorRes
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
    
    public function deleteColaborador($id){

    }

    public function transformColaboradorToJson(User $colaborador, DetalleColaborador $colaborador_ext){
        return [
                'nombre' => $colaborador->nombre,
                'apellido'=>$colaborador->apellido,
                'email'=> $colaborador->email,
                'puesto'=> $colaborador_ext->puesto,
                'telefono'=> $colaborador_ext->telefono,
                'fecha_nacimiento'=>$colaborador_ext->fecha_nacimiento,
        ];
    }
}

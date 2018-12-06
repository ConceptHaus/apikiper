<?php

namespace App\Http\Controllers\Forms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Extras\IntegracionForm;

use Mailgun;
use DB;
use Mail;
use Keygen;
use URL;
use Twilio\Rest\Client;

class FormsController extends Controller
{
    
    public function addNew(Request $request){
        $validator = $this->validatorForm($request->all());
         $key =  Keygen::alphanum(32)->generate();
         $url_success = $request->url_success;
         $url_error = $request->url_error;
         $nombre_form = $request->nombre; 
        if($validator->passes()){
            
            try{
                 
                DB::beginTransaction();
                
                $newForm = New IntegracionForm();
                $newForm->token = $key;
                $newForm->url_success = $url_success;
                $newForm->url_error = $url_error;
                $newForm->nombre = $nombre_form;
                $newForm->save();

                DB::commit();
                
                return response()->json([
                    'error'=>false,
                    'messages'=>'Successfully created',
                    'data'=>$newForm::where('id_integracion_forms',$newForm->id_integracion_forms)->first()
                ],200);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'messages'=>$e

                ],400);
            }
           

        }
        $errors = $validator->errors()->toArray();
        return response()->json([
            'error'=>true,
            'messages'=> $errors

        ],400);

    }

    public function getAll(){
        $allForms = IntegracionForm::all();

        return response()->json([
            'error'=>false,
            'messages'=>'Success',
            'data'=>$allForms
        ]);
    }
    
    public function getOne($id){
        $form = IntegracionForm::where('id_integracion_forms',$id)->first();

        return response()->json([
            'error'=>false,
            'messages'=>'Success',
            'data'=>$form,
            'url'=>URL::to('/api/v1/forms/register').'?'.$form->token
        ]);
    }

    public function registerProspecto(Request $request){
        $token = $request->query('token');
        $verify = IntegracionForm::where('token',$token)->first();
        
        $nombre = $request->nombre;
        $apellido = $request->apellido;
        $empresa = $request->empresa;
        $email = $request->correo;
        $telefono = $request->telefono;
        $mensaje = $request->mensaje;
        $fuente = $request->fuente;

        
        if($verify){
            
            try{
                
                DB::beginTransaction();

                $prospecto = new Prospecto();
                $prospecto->nombre = $nombre;
                $prospecto->apellido = $nombre;
                $prospecto->correo = $email;  
                $prospecto->fuente = $fuente;  
                $prospecto->save();
                
                $detalleProspecto = new DetalleProspecto();
                $detalleProspecto->empresa = $empresa;
                $detalleProspecto->telefono = $telefono;
                $detalleProspecto->nota = $mensaje;
                $prospecto->detalle_prospecto()->save($detalleProspecto);

                $verify->total += 1;
                $verify->save();

                DB::commit();
                
                return redirect()->away($verify->url_success);

            }catch(Exception $e){
                
                DB::rollBack();
                
                return redirect()->away($verify->url_error);

            }
            
        
        }
        
        return redirect()->away($verify->url_error);
    }

    public function validatorForm(array $data){
        return Validator::make($data, [
            'nombre'=>'required|string',
            'url_success'=>'required|string',
            'url_error'=>'required|string'
        ]);
    }

}

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
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Prospecto\CallsProstecto;
use App\Modelos\Extras\IntegracionForm;
use App\Modelos\Prospecto\CampaignInfo;

use App\Modelos\Extras\Etiqueta;
use App\Modelos\Prospecto\EtiquetasProspecto;

use App\Modelos\Colaborador\EtiquetaColaborador;
use App\Modelos\Prospecto\ColaboradorProspecto;

use Mailgun;
use DB;
use Mail;
use Keygen;
use URL;
use Twilio\Rest\Client;

use App\Events\NewLead;
use App\Events\NewCall;
use App\Events\NewAssigment;
use App\Events\CoCan;
use App\Events\CoGdl;
use App\Events\Event;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
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
                Bugsnag::notifyException(new RuntimeException("No se pudo crear una integración"));
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

        $campaign = DB::table('integracion_forms')->join('campaign_infos','campaign_infos.id_forms','integracion_forms.id_integracion_forms')
                    ->where('integracion_forms.id_integracion_forms',$id)
                    ->select(DB::raw('count(*) as leads, campaign_infos.utm_campaign'),'campaign_infos.utm_term')
                    ->groupBy('campaign_infos.utm_campaign')->get();
        $form = IntegracionForm::where('id_integracion_forms',$id)->first();
        $prospectos = IntegracionForm::all()->count();
        return response()->json([
            'error'=>false,
            'messages'=>'Success',
            'data'=>$form,
            'campaign'=>$campaign,
            'total_prospectos'=>$prospectos,
            'url'=>URL::to('/api/v1/forms/register').'?token='.$form['token']
        ]);
    }
    

    public function registerProspecto(Request $request){
      // return $request->query('token');
        $token = $request->query('token');

        $verify = IntegracionForm::where('token',$token)->first();

        if($verify){
            try{
              
              if($this->validadorProspecto($request->all())->passes()){
                  
                $this->addProspecto($request->all(),$verify->id_integracion_forms);
                  
                    return response()->json([
                      'message'=>'Success',
                      'error'=>false
                    ],201);

              }
              
              else{
                return response()->json([
                  'message'=>'Error: email repetido',
                  'error'=>true
                ],406);
              }
              

            }catch(Exception $e){

              return response()->json([
                'message'=>'Error',
                'error'=>true
              ],401);
            }
        }

        return response()->json([
                  'message'=>'Error',
                  'error'=>true
        ], 401);
    }

    public function updateForm(Request $request){

      $validator = $this->validatorUpdate($request->all());

      if ($validator->passes()) {
        try {
          DB::beginTransaction();
          $form = IntegracionForm::where('id_integracion_forms',$request->id)->first();
          $form->url_success = $request->url_success;
          $form->url_error = $request->url_error;
          $form->nombre = $request->nombre;
          $form->save();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Integración actualizada correctamente.'
          ],200);

        } catch (Exception $e) {
          DB::rollBack();
          Bugsnag::notifyException(new RuntimeException("No se pudo actualizar una integración"));
          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);

        }
      }

      $errors = $validator->errors()->toArray();

      return response()->json([
        'error'=>true,
        'message'=>$errors
      ],400);

    }

    public function deleteForm($id){

        try {
          DB::beginTransaction();
          $form = IntegracionForm::where('id_integracion_forms',$id)->first();
          $form->delete();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Integración borrada correctamente.'
          ],200);

        } catch (Exception $e) {

          DB::rollBack();
          Bugsnag::notifyException(new RuntimeException("No se pudo eliminar una integración"));
          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }

    }

    public function check_etiquetas($id_etiqueta){

        $users = EtiquetaColaborador::where('id_etiqueta',$id_etiqueta)->get();
        $users_array = [];

        for($i=0; $i < count($users); $i++){

          array_push($users_array, $users[$i]['id_user']);
        }

        return $users_array;
    }

    public function random_assigment($id_prospecto){
        $users = DB::table('users')->select('id')->where([['super_admin','!=',1],['email','!=','admin@concepthaus.mx']])->get();
        $arr_users = array();

        foreach($users as $user){
          array_push($arr_users, $user->id);
        }

        $random_user = array_rand($arr_users,1);

        $col_prospecto = new ColaboradorProspecto;
        $col_prospecto->id_colaborador = $arr_users[$random_user];
        $col_prospecto->id_prospecto = $id_prospecto;
        $col_prospecto->save();

        return $arr_users[$random_user];
    }

    public function assigment_colaborador($users_array, $id_prospecto){
      if(count($users_array)>0){
        for($i=0; $i < count($users_array); $i++){

          $col_prospecto = new ColaboradorProspecto;
          $col_prospecto->id_colaborador = $users_array[$i];
          $col_prospecto->id_prospecto = $id_prospecto;
          $col_prospecto->save();

        }

        return true;
      }
      
      return false;

    }

    public function addProspecto(array $data, $verify){
        
        DB::beginTransaction();

        $prospecto = new Prospecto();
        $status = new StatusProspecto();

        if(isset($data['lead_type'])){
          $prospecto->nombre = $data['caller_name'];
          $prospecto->correo = $data['correo']?: 'Not set';
          $prospecto->fuente = $data['fuente'] ?: 4;
          $prospecto->save();
          
          if(isset($data['assigment'])){
            $data_event['prospecto'] = $prospecto;
            $data_event['desarrollo'] = $data['assigment'];
            event(new NewAssigment($data_event));
          }

          //Call
          $llamadaProspecto = New CallsProstecto();
          $llamadaProspecto->caller_number = $data['caller_number'];
          $llamadaProspecto->caller_name = $data['caller_name'];
          $llamadaProspecto->caller_city = (isset($data['caller_city']) ? $data['caller_city'] : '');
          $llamadaProspecto->caller_state = (isset($data['caller_state']) ? $data['caller_state'] : '');
          $llamadaProspecto->caller_zip = (isset($data['caller_zip']) ? $data['caller_zip'] : '');
          $llamadaProspecto->play_recording = (isset($data['recording']) ? $data['recording'] : 'not set');
          $llamadaProspecto->device_type = (isset($data['device_type']) ? $data['device_type'] : '');
          $llamadaProspecto->device_make = (isset($data['device_make']) ? $data['device_make'] : '');
          $llamadaProspecto->call_status = $data['call_status'];
          $llamadaProspecto->call_duration = $data['call_duration'];
          $prospecto->calls()->save($llamadaProspecto);

          $detalleProspecto = new DetalleProspecto();
          $detalleProspecto->telefono = preg_replace('/[^0-9]+/','',$data['caller_number']);
          $detalleProspecto->celular = preg_replace('/[^0-9]+/','',$data['caller_number']);
          $detalleProspecto->whatsapp = preg_replace('/[^0-9]+/','',$data['caller_number']);
          $prospecto->detalle_prospecto()->save($detalleProspecto);

          $status->id_cat_status_prospecto = 1;
          $prospecto->status_prospecto()->save($status);



          if(isset($data['lead_campaign'])){

            $campaign = new CampaignInfo();
            $campaign->utm_term = (isset($data['lead_keyword']) ? $data['lead_keyword'] : ' ');
            $campaign->utm_campaign = (isset($data['lead_campaign']) ? $data['lead_campaign'] : 'orgánico');
            $campaign->id_forms = $verify;
            $prospecto->campaign()->save($campaign);

            $etiqueta = Etiqueta::where('nombre','=',$campaign->utm_campaign)->first();
            
            if(!$etiqueta){
                $etiqueta = new Etiqueta;
                $etiqueta->nombre = $campaign->utm_campaign;
                $etiqueta->status = 1;
                $etiqueta->save();
            }

            $etiqueta_prospecto = new EtiquetasProspecto;
            $etiqueta_prospecto->id_etiqueta = $etiqueta->id_etiqueta;
            $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto);
        }

        event(new NewCall($prospecto));

        }else{

          $prospecto->nombre = $data['nombre'];
          $prospecto->apellido = (isset($data['apellido']) ? $data['apellido'] : '');
          $prospecto->correo = $data['correo'];
          $prospecto->fuente = (isset($data['fuente']) ? $data['fuente'] : 2) ;
          $prospecto->save();

          
          $detalleProspecto = new DetalleProspecto();
          $detalleProspecto->empresa = (isset($data['empresa']) ? $data['empresa'] : '');
          $detalleProspecto->telefono = (isset($data['telefono']) ? preg_replace('/[^0-9]+/','',$data['telefono']) : '');
          $detalleProspecto->celular = (isset($data['telefono']) ? preg_replace('/[^0-9]+/','',$data['telefono']) : '');
          $detalleProspecto->whatsapp = (isset($data['telefono']) ? preg_replace('/[^0-9]+/','',$data['telefono']) : '');
          $detalleProspecto->nota = (isset($data['mensaje']) ? $data['mensaje'] : '');
          $prospecto->detalle_prospecto()->save($detalleProspecto);
          
          $status->id_cat_status_prospecto = 2;
          $prospecto->status_prospecto()->save($status);

          if(isset($data['utm_campaign'])){

            $campaign = new CampaignInfo();
            $campaign->utm_term = (isset($data['utm_term']) ? $data['utm_term'] : 'orgánico' );
            $campaign->utm_campaign = $data['utm_campaign'];
            $campaign->id_forms = $verify;
            $prospecto->campaign()->save($campaign);

            $etiqueta_campaign = Etiqueta::where('nombre','=',$campaign->utm_campaign)->first();
            $etiqueta_term = Etiqueta::where('nombre','=',$campaign->utm_term)->first();

          if(!$etiqueta_campaign){
              $etiqueta_campaign = new Etiqueta;
              $etiqueta_campaign->nombre = $campaign->utm_campaign;
              $etiqueta_campaign->status = 1;
              $etiqueta_campaign->save();
          }
          if(!$etiqueta_term){
              $etiqueta_term = new Etiqueta;
              $etiqueta_term->nombre = $campaign->utm_term;
              $etiqueta_term->status = 1;
              $etiqueta_term->save();
          }

          $etiqueta_prospecto_c = new EtiquetasProspecto;
          $etiqueta_prospecto_c->id_etiqueta = $etiqueta_campaign->id_etiqueta;
          $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto_c);


          $etiqueta_prospecto_t = new EtiquetasProspecto;
          $etiqueta_prospecto_t->id_etiqueta = $etiqueta_term->id_etiqueta;
          $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto_t);

          
          //$array_users = $this->check_etiquetas($etiqueta_prospecto_c->id_etiqueta);
          //$user_rand = $this->random_assigment($prospecto->id_prospecto);
          //$assigment = $this->assigment_colaborador($array_users, $prospecto->id_prospecto);
          //$data_event['colaboradores'] = $user_rand;
          
          // if($assigment){
          //    event(new NewAssigment($data_event));
          // }

          // if($user_rand){
          //   event(new NewAssigment($data_event));
          // }
  
          event(new NewLead($prospecto));
          
 
        }
        if(isset($data['assigment'])){
          $data_event['prospecto'] = $prospecto;
          $data_event['desarrollo'] = $data['assigment'];
          event(new NewAssigment($data_event));
        }
        

        }
        
        DB::commit();

        
        
        

    }




    //Validadores
    public function validatorUpdate(array $data){
      return Validator::make($data, [
        'id'=>'required|exists:integracion_forms,id_integracion_forms',
        'nombre'=>'required|string',
        'url_success'=>'required|string',
        'url_error'=>'required|string'
      ]);
    }

    public function validadorProspecto(array $data){
        return Validator::make($data,[
          'correo'=>'required|email|max:255|unique:prospectos,correo',
        ]);
    }
    public function validatorForm(array $data){
        return Validator::make($data, [
            'nombre'=>'required|string',
            'url_success'=>'required|string',
            'url_error'=>'required|string'
        ]);
    }


}

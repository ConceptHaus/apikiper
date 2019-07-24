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

use Mailgun;
use DB;
use Mail;
use Keygen;
use URL;
use Twilio\Rest\Client;

use App\Events\NewLead;
use App\Events\NewCall;
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
              
              $this->addProspecto($request->all(),$verify->id_integracion_forms);
              
              return response()->json([
                 'message'=>'Success',
                'error'=>false
              ],201);

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

    

    public function addProspecto(array $data, $verify){
        
        DB::beginTransaction();

        $prospecto = new Prospecto();
        $status = new StatusProspecto();

        if(isset($data['lead_type'])){
          $prospecto->nombre = $data['caller_name'];
          $prospecto->correo = 'Not set';
          $prospecto->fuente = 4;
          $prospecto->save();
          
          //Call
          $llamadaProspecto = New CallsProstecto();
          $llamadaProspecto->caller_number = $data['caller_number'];
          $llamadaProspecto->caller_name = $data['caller_name'];
          $llamadaProspecto->caller_city = $data['caller_city'];
          $llamadaProspecto->caller_state = $data['caller_state'];
          $llamadaProspecto->caller_zip = $data['caller_zip'];
          $llamadaProspecto->play_recording = $data['recording'];
          $llamadaProspecto->device_type = $data['device_type'];
          $llamadaProspecto->device_make = $data['device_make'];
          $llamadaProspecto->call_status = $data['call_status'];
          $llamadaProspecto->call_duration = $data['call_duration'];
          $prospecto->calls()->save($llamadaProspecto);

          $detalleProspecto = new DetalleProspecto();
          $detalleProspecto->telefono = $data['caller_number'];
          $detalleProspecto->celular = $data['caller_number'];
          $detalleProspecto->whatsapp = $data['caller_number'];
          $prospecto->detalle_prospecto()->save($detalleProspecto);

          $status->id_cat_status_prospecto = 1;
          $prospecto->status_prospecto()->save($status);



          if(isset($data['lead_campaign'])){

          $campaign = new CampaignInfo();
          $campaign->utm_term = $data['lead_keyword'];
          $campaign->utm_campaign = $data['lead_campaign'];
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
          if(isset($data['apellido'])){
            $prospecto->apellido = $data['apellido'];
          }
          $prospecto->correo = $data['correo'];
          $prospecto->fuente = $data['fuente'];
          $prospecto->save();

          
          $detalleProspecto = new DetalleProspecto();
          if(isset($data['empresa'])){
            $detalleProspecto->empresa = $data['empresa'];
          }
          $detalleProspecto->telefono = $data['telefono'];
          $detalleProspecto->celular = $data['telefono'];
          $detalleProspecto->whatsapp = $data['telefono'];
          if(isset($data['mensaje'])){
            $detalleProspecto->nota = $data['mensaje'];
          }
          $prospecto->detalle_prospecto()->save($detalleProspecto);
          
          $status->id_cat_status_prospecto = 2;
          $prospecto->status_prospecto()->save($status);

          if(isset($data['utm_campaign'])){

          $campaign = new CampaignInfo();
          $campaign->utm_term = $data['utm_term'];
          $campaign->utm_campaign = $data['utm_campaign'];
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

          
          if(strpos($data['utm_campaign'],'can') !== false){
            event(new CoCan($prospecto));
          }
          if(strpos($data['utm_campaign'],'gdl') !== false){
            event(new CoGdl($prospecto));
          }
          
        }

        event(new NewLead($prospecto));

        }
        
        DB::commit();

        
        
        //Condicional etiqueta Colabora

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

    public function validatorForm(array $data){
        return Validator::make($data, [
            'nombre'=>'required|string',
            'url_success'=>'required|string',
            'url_error'=>'required|string'
        ]);
    }


}

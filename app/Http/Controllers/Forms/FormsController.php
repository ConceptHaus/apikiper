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
                    ->select(DB::raw('count(*) as leads, campaign_infos.utm_term'),'campaign_infos.utm_campaign')
                    ->groupBy('campaign_infos.utm_term')->get();
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

        $nombre = $request->nombre;
        $apellido = $request->apellido;
        $empresa = $request->empresa;
        $email = $request->correo;
        $telefono = $request->telefono;
        $mensaje = $request->mensaje;
      
        if($request->utm_campaign != null){

          $utm_campaign = $request->utm_campaign;

        }else{
          $utm_campaign = 'Orgánico';
        }

        if($request ->utm_term != null){
          
          $utm_term = $request ->utm_term;

        }else{
          $utm_term = 'Orgánico';
        }

        if($request->fuente != null){
            $fuente = $request->fuente;
        }else{
          $fuente = 2;
        }
        


        if($verify){

          if($request->lead_type == "Phone Call"){
            
            try{

              DB::beginTransaction();
              $prospecto = New Prospecto();
              $prospecto->nombre = $request->caller_name;
              $prospecto->correo = "Not Set";
              $prospecto->fuente = 4;
              $prospecto->save();

              $detalleProspecto = New DetalleProspecto();
              $detalleProspecto->telefono = $request->caller_number;
              $prospecto->detalle_prospecto()->save($detalleProspecto);

              $llamadaProspecto = New CallsProstecto();
              $llamadaProspecto->caller_number = $request->caller_number;
              $llamadaProspecto->caller_name = $request->caller_name;
              $llamadaProspecto->caller_city = $request->caller_city;
              $llamadaProspecto->caller_state = $request->caller_state;
              $llamadaProspecto->caller_zip = $request->caller_zip;
              $llamadaProspecto->play_recording = $request->play_recording;
              $llamadaProspecto->device_type = $request->device_type;
              $llamadaProspecto->device_make = $request->device_make;
              $llamadaProspecto->call_status = $request->call_status;
              $llamadaProspecto->call_duration = $request->call_duration;
              $prospecto->calls()->save($llamadaProspecto);

              $status = New StatusProspecto();
              $status->id_cat_status_prospecto = 1;
              $prospecto->status_prospecto()->save($status);

              $campaign = New CampaignInfo();
              $campaign->id_forms = $verify->id_integracion_forms;
              $campaign->utm_campaign = $request->lead_campaign;
              $campaign->utm_term = $request->lead_keyword;
              $campaign->utm_source = $request->lead_source;
              $prospecto->campaign()->save($campaign);

              DB::commit();

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
              

          }else{

            try{

                DB::beginTransaction();

                $prospecto = new Prospecto();
                $prospecto->nombre = $nombre;
                $prospecto->apellido = $apellido;
                $prospecto->correo = $email;
                $prospecto->fuente = $fuente;
                $prospecto->save();

                $detalleProspecto = new DetalleProspecto();
                $detalleProspecto->empresa = $empresa;
                $detalleProspecto->telefono = $telefono;
                $detalleProspecto->nota = $mensaje;
                $prospecto->detalle_prospecto()->save($detalleProspecto);

                $status = new StatusProspecto();
                $status->id_cat_status_prospecto = 2;
                $prospecto->status_prospecto()->save($status);

                $campaign = new CampaignInfo();
                $campaign->utm_term = $utm_term;
                $campaign->utm_campaign = $utm_campaign;
                $campaign->id_forms = $verify->id_integracion_forms;
                $prospecto->campaign()->save($campaign);
                
                if(Etiqueta::where('nombre','=',$utm_campaign)->first()){ 
                  $etiqueta = Etiqueta::where('nombre','=',$utm_campaign)->first();
                }else{
                  $etiqueta = new Etiqueta;
                  $etiqueta->nombre = $utm_campaign;
                  $etiqueta->status = 1;
                  $etiqueta->save();
                }
                
                $etiqueta_prospecto = new EtiquetasProspecto;
                $etiqueta_prospecto->id_etiqueta = $etiqueta->id_etiqueta;
                $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto);

                $verify->total += 1;
                $verify->save();

                DB::commit();
                
                  
                //Mail New Lead
                event(new NewLead($prospecto));


                return response()->json([
                  'message'=>'Success',
                  'error'=>false
                ]);

            }catch(Exception $e){

                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("La integración no está registrando prospectos"));
                return response()->json([
                  'message'=>'Error',
                  'error'=>true
                ]);

            }

          }

            


        }

        return response()->json([
                  'message'=>'Error',
                  'error'=>true
                ]);
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

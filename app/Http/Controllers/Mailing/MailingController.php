<?php

namespace App\Http\Controllers\Mailing;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Modelos\Mailing\Mailings;
use App\Modelos\Mailing\DetalleMailings;
use App\Modelos\Mailing\ImagesMailings;
use Illuminate\Support\Facades\Validator;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\StatusProspecto;


use Mailgun;
use DB;

class MailingController extends Controller
{
    // protected function validadorCamapaña(array $data){
    //   return Validator::make($data, [
    //     ''
    //   ]);
    // }

    public function addNew(Request $request){

      $campaña = $request->all();

      try {
        DB::beginTransaction();
        $campana = new Mailings;
        $campana->titulo_campana = $request->nombre_campana;
        $campana->save();

        $mailing = new DetalleMailings;
        $mailing->subject = $request->titulo;
        $mailing->subtitle = $request->subtitulo;
        $mailing->preview_text = $request->texto_previo;
        $mailing->text_body = $request->descripcion;
        if ($request->nombre_boton) {
          $mailing->cta_nombre = $request->nombre_boton;
        }
        $mailing->cta_url = $request->url;
        $mailing->color = $request->color;
        if ($request->color_fuente) {
          $mailing->color_fuente = $request->color_fuente;
        }
        if ($request->color_cta) {
          $mailing->color_cta = $request->color_cta;
        }
        $campana->detalle()->save($mailing);
        DB::commit();

        //Query para obtener lista de remitentes
        if($request->servicio == 0 && $request->etiqueta == 0){
          $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->status)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
          

        }else{
          if($request->servicio != 0){

            $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->status)
                        ->where('servicio_oportunidad.id_servicio_cat',$request->servicio)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
            
            
          }
          elseif($request->etiqueta != 0){
               $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->status)
                        ->where('etiquetas_oportunidades.id_etiqueta',$request->etiqueta)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get();

              
          }
          
          $send_contacts = array();
          foreach ($remitentes as $remitente) {
              array_push($send_contacts, [$remitente->correo =>['name'=>$remitente->nombre]]);
          }
          
          $send_contacts = $send_contacts[0];
        }
        
        $detalle = DetalleMailings::where('id_detalle',$mailing->id_detalle)->first();

        $datosMail['contenido'] = $request->descripcion;
        $datosMail['asunto'] = $request->titulo;
        $datosMail['email'] = $send_contacts;
        $datosMail['color'] = $request->color;
        $datosMail['color_fuente'] = $detalle->color_fuente;
        $datosMail['subtitulo'] = $detalle->subtitle;
        $datosMail['cta_nombre'] = $detalle->cta_nombre;
        $datosMail['color_cta'] = $detalle->color_cta;
        $datosMail['cta_link'] = $detalle->cta_url;
        $datosMail['titulo_campana'] = $campana->titulo_campana;
        Mailgun::send('mailing.template_one', $datosMail, function($message) use ($datosMail){
              $message->to($datosMail['email']);
              $message->subject($datosMail['asunto']);
              $message->trackClicks(true);
              $message->trackOpens(true);
              $message->tag($datosMail['titulo_campana']);

          });

        return response()->json([
          'message'=>'Camapaña guardada correctamente.',
          'error'=>false
        ],200);
      } catch (Exception $e) {
        return response()->json([
          'message'=>$e,
          'error'=>true
        ],400);
      }

    }

    public function getAll(){
      $mailings = Mailings::getAll();

      if ($mailings) {
        return response()->json([
          'message'=>'Mailings obtenidos correctamente',
          'error'=>false,
          'data'=>$mailings
        ],200);
      }

      return response()->json([
        'message'=>'No hay mailings',
        'error'=>true,
      ],400);

    }

    public function getOne($id){
      $mailing = Mailings::getOne($id);

      if ($mailing) {
        return response()->json([
          'message'=>'Mailing obtenido correctamente',
          'error'=>false,
          'data'=>$mailing
        ],200);
      }

      return response()->json([
        'message'=>'No hay mailings',
        'error'=>true,
      ],400);

    }
}

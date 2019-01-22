<?php

namespace App\Http\Controllers\Mailing;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Modelos\Mailing\Mailings;
use App\Modelos\Mailing\DetalleMailings;
use App\Modelos\Mailing\ImagesMailings;
use Illuminate\Support\Facades\Storage;
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
      /*
      if($request->image1 == null || $request->image2)
      {
        return response('No ingresaste alguna imagen, completa el campo', 400);
      }
      */
      return response()->json([
        'REQUEST'=>$request->all()
      ],400);

      try {
        DB::beginTransaction();
        $campana = new Mailings;
        $campana->titulo_campana = $request->nombre_campana;

        $mailing = new DetalleMailings;
        $mailing->subject = $request->titulo;
        $mailing->subtitle = $request->subtitulo;
        $mailing->preview_text = "";
        $mailing->text_body = $request->descripcion;
        if ($request->nombre_boton) {
          $mailing->cta_nombre = $request->nombre_boton;
        }
        $mailing->cta_url = $request->url_boton;
        if ($request->color_fuente) {
          $mailing->color_fuente = $request->color_fuente;
        }
        if ($request->color_cta) {
          $mailing->color_cta = $request->color_cta;
        }
        if($request->opcionEstatus)
        {
          $mailing->opcion_status = $request->opcionEstatus;
        }      
        if($request->opcionEtiqueta)
        {
          $mailing->opcion_etiqueta = $request->opcionEtiqueta;
        }
        if($request->opcionServicio)
        {
          $mailing->opcion_servicio = $request->opcionServicio;
        }
        if($request->fondo_general){
          $mailing->fondo_general = $request->fondo_general;
        }
        if($request->fondo_boton){
          $mailing->fondo_cta = $request->fondo_boton;
        }
        if($request->color_titulo){
          $mailing->color_titulo = $request->color_titulo; 
        }
        if($request->color_subtitulo){
          $mailing->color_subtitulo = $request->color_subtitulo;
        }
        if($request->color_lineas){
          $mailing->color_lineas = $request->color_lineas;
        }

        

        //Query para obtener lista de remitentes
        if($request->opcionServicio == 0 && $request->opcionEtiqueta == 0)
        {
          $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('oportunidad_prospecto.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->opcionEstatus)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
        }
        else
        {
          if($request->opcionServicio != 0 && $request->opcionEtiqueta != 0)
          {
            $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('oportunidad_prospecto.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->whereNull('servicio_oportunidad.deleted_at')
                        ->whereNull('etiquetas_oportunidades.deleted_at')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->opcionEstatus)
                        ->where('servicio_oportunidad.id_servicio_cat',$request->opcionServicio)
                        ->where('etiquetas_oportunidades.id_etiqueta',$request->opcionEtiqueta)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
          }
          elseif($request->opcionServicio != 0)
          {
            $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('oportunidad_prospecto.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->whereNull('servicio_oportunidad.deleted_at')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->opcionEstatus)
                        ->where('servicio_oportunidad.id_servicio_cat',$request->opcionServicio)
                        ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
          }
          elseif($request->opcionEtiqueta != 0)
          {
            $remitentes = DB::table('prospectos')
                        ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                        ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('oportunidad_prospecto.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->whereNull('etiquetas_oportunidades.deleted_at')
                        ->where('status_oportunidad.id_cat_status_oportunidad',$request->opcionEstatus)
                        ->where('etiquetas_oportunidades.id_etiqueta',$request->opcionEtiqueta)
                        ->select('prospectos.correo','prospectos.nombre','prospectos.id_prospecto')->distinct()->get();  
          }
        }
        
        $numero_remitentes = count($remitentes);

        if($numero_remitentes > 0)
        {
          $campana->enviados = $numero_remitentes;
          $campana->save();
          $campana->detalle()->save($mailing);
          
          if(isset($request->image1))
          {
            $image1 = new ImagesMailings();
            $image1->url = $this->uploadFilesS3($request->image1,$campana->id_mailing,1);
            $campana->imagenes()->save($image1);
            $datosMail['image1'] = $image1->url;
          }
          if(isset($request->image2))
          {
            $image2 = new ImagesMailings();
            $image2->url = $this->uploadFilesS3($request->image2,$campana->id_mailing,2);
            $campana->imagenes()->save($image2);
            $datosMail['image2'] = $image2->url;
          }
          DB::commit();
          
          $send_contacts = array();
          foreach ($remitentes as $remitente) {
            array_push($send_contacts, [$remitente->correo => ['name'=>$remitente->nombre]]);
          }
          
          $datosMail['contenido'] = $request->descripcion;
          $datosMail['asunto'] = $request->titulo;
          $datosMail['email'] = $send_contacts;
          $datosMail['color'] = $request->color_lineas;
          $datosMail['color_fuente'] = $mailing->color_fuente;
          $datosMail['subtitulo'] = $mailing->subtitle;
          $datosMail['cta_nombre'] = $mailing->cta_nombre;
          $datosMail['color_cta'] = $mailing->color_cta;
          $datosMail['cta_link'] = $mailing->cta_url;
          $datosMail['titulo_campana'] = $campana->titulo_campana;
          $datosMail['fondo_general'] = $mailing->fondo_general;
          $datosMail['fondo_cta'] = $mailing->fondo_cta;
          $datosMail['color_titulo'] = $mailing->color_titulo;
          $datosMail['color_subtitulo'] = $mailing->color_subtitulo;          
          for($i=0;$i<$numero_remitentes;$i++)
          {
            $datosMail['email'] = $send_contacts[$i];
            
            Mailgun::send('mailing.template_one', $datosMail, function($message) use ($datosMail){
                    $message->to($datosMail['email']);
                    $message->subject($datosMail['asunto']);
                    $message->trackClicks(true);
                    $message->trackOpens(true);
                    $message->tag($datosMail['titulo_campana']);

            });

          }
          return response()->json([
            'message'=>'Camapaña guardada correctamente.',
            'error'=>false
          ],200);
        }
        DB::rolback();
        return response()->json([
          'message'=>'No hay remitentes.',
          'error'=>true
        ],400);
      } catch (Exception $e) {
        DB::rolback();
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
    public function uploadFilesS3($file, $mailing,$numero){
      //Sube archivos a bucket de Amazon
      $disk = Storage::disk('s3');
      $path = $file->store('mailing/imagenes/'.$mailing.$numero,'s3');
      Storage::setVisibility($path,'public');
      return $disk->url($path);
  }
}

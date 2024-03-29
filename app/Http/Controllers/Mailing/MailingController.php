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
use Illuminate\Support\Facades\File;
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
      if($request->opcionEtiqueta == 'undefined')
        $opcion_etiqueta = 0;
      else
        $opcion_etiqueta = $request->opcionEtiqueta;
      if($request->opcionServicio == 'undefined')
        $opcion_servicio = 0;
      else
        $opcion_servicio = $request->opcionServicio;
      if($request->opcionEstatus == 'undefined')
        $opcion_estatus = 0;
      else
        $opcion_estatus = $request->opcionEstatus;
      
      try {
        DB::beginTransaction();
        $campana = new Mailings;
        $campana->titulo_campana = $request->nombre_campana;

        $mailing = new DetalleMailings;
        $mailing->subject = $request->titulo;
        $mailing->subtitle = $request->subtitulo;
        $mailing->preview_text = "";
        $mailing->from = "pruebas123456@gmail.com";
        
        if($request->description == 'undefined')
          $mailing->text_body = null;
        else
          $mailing->text_body = $request->descripcion;
        
        if($request->nombre_boton == 'undefined')
          $mailing->cta_nombre = null;
        else
          $mailing->cta_nombre = $request->nombre_boton;

        if($request->url_boton == 'undefined')
          $mailing->cta_url = null;
        else
          $mailing->cta_url = $request->url_boton;
        
        if($request->color_fuente == 'undefined')
          $mailing->color_fuente = null;
        else
          $mailing->color_fuente = $request->color_fuente;
        
        if ($request->color_cta == 'undefined')
          $mailing->color_cta = null;
        else
          $mailing->color_cta = $request->color_cta;
        
        if($request->$opcion_estatus == 'undefined')
          $mailing->$opcion_status = null;
        {
          $mailing->opcion_status = $opcion_estatus;
        }      
        if($opcion_etiqueta)
        {
          $mailing->opcion_etiqueta =  $opcion_etiqueta;
        }
        if($request->$opcion_servicio == 'undefined')
          $mailing->$opcion_servicio = null;
        {
          $mailing->opcion_servicio =  $opcion_servicio;
        }
        
        if($request->fondo_general == 'undefined'){
          $mailing->fondo_general = null;
        }
        else
          $mailing->fondo_general = $request->fondo_general;

        
        if($request->fondo_boton == 'undefined'){
          $mailing->fondo_cta = null;
        }
        else
          $mailing->fondo_cta = $request->fondo_boton;

        
        if($request->color_titulo == 'undefined'){
          $mailing->color_titulo = null; 
        }
        else
          $mailing->color_titulo = $request->color_titulo; 

        
        if($request->color_subtitulo){
          $mailing->color_subtitulo = null;
        }
        else
          $mailing->color_subtitulo = $request->color_subtitulo;

        
        if($request->color_lineas){
          $mailing->color_lineas = null;
        }
        else
          $mailing->color_lineas = $request->color_lineas;


        //Query para obtener lista de remitentes
        if($opcion_estatus != 0){
          if($opcion_servicio == 0 && $opcion_etiqueta == 0)
          {
            $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
          }
          else
          {
            if($opcion_servicio != 0 && $opcion_etiqueta != 0)
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
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                          ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_servicio != 0)
            {
              $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->whereNull('servicio_oportunidad.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_etiqueta != 0)
            {
              $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->whereNull('etiquetas_oportunidades.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                          ->select('prospectos.correo','prospectos.nombre','prospectos.id_prospecto')->distinct()->get();  
            }
          }
        } else {
          if($opcion_servicio == 0 && $opcion_etiqueta == 0)
          {
            $remitentes = DB::table('prospectos')
              ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
              ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
              ->whereNull('prospectos.deleted_at')
              ->whereNull('oportunidad_prospecto.deleted_at')
              ->whereNull('status_oportunidad.deleted_at')
              ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
          }
          else
          {
            if($opcion_servicio != 0 && $opcion_etiqueta != 0)
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
                ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_servicio != 0)
            {
              $remitentes = DB::table('prospectos')
                ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                ->whereNull('prospectos.deleted_at')
                ->whereNull('oportunidad_prospecto.deleted_at')
                ->whereNull('status_oportunidad.deleted_at')
                ->whereNull('servicio_oportunidad.deleted_at')
                ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_etiqueta != 0)
            {
              $remitentes = DB::table('prospectos')
                ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                ->whereNull('prospectos.deleted_at')
                ->whereNull('oportunidad_prospecto.deleted_at')
                ->whereNull('status_oportunidad.deleted_at')
                ->whereNull('etiquetas_oportunidades.deleted_at')
                ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                ->select('prospectos.correo','prospectos.nombre','prospectos.id_prospecto')->distinct()->get();  
            }
          }
        }
        $numero_remitentes = count($remitentes);

        if($numero_remitentes > 0)
        {
          $campana->enviados = $numero_remitentes;
          $campana->save();
          $campana->detalle()->save($mailing);
          

          //guarda la imagen, debemos corregir error en angular para enviar los datos
          
          if($request->file('image1')->isValid())
          {
            $image1 = new ImagesMailings();
            $image1->url = $this->uploadFilesS3($request->image1,$campana->id_mailing,1);
            $campana->imagenes()->save($image1);
            $datosMail['image1'] = $image1->url;
          }
          if($request->file('image2')->isValid())
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
          Mailgun::send('mailing.template_one', $datosMail, function($message) use ($datosMail){
                    foreach($datosMail['email'] as $to_){
                      $message->to($to_);
                    }
                    $message->subject($datosMail['asunto']);
                    $message->trackClicks(true);
                    $message->trackOpens(true);
                    $message->tag($datosMail['titulo_campana']);
            });

          return response()->json([
            'message'=>'Newsletter enviado correctamente.',
            'error'=>false
          ],200);
        }
        DB::rollback();
        return response()->json([
          'message'=>'No hay remitentes.',
          'error'=>true
        ],400);
      } catch (Exception $e) {
        DB::rollback();
        return response()->json([
          'message'=>$e,
          'error'=>true,
          'request'=>$request->all()
        ],400);
      }

    }

    public function checkRemitentes(Request $request){
        if($request->opcionEtiqueta == 'undefined')
          $opcion_etiqueta = 0;
        else
          $opcion_etiqueta = $request->opcionEtiqueta;
        if($request->opcionServicio == 'undefined')
          $opcion_servicio = 0;
        else
          $opcion_servicio = $request->opcionServicio;
        if($request->opcionEstatus == 'undefined')
          $opcion_estatus = 0;
        else
          $opcion_estatus = $request->opcionEstatus;


         if($opcion_estatus != 0){
          if($opcion_servicio == 0 && $opcion_etiqueta == 0)
          {
            $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
          }
          else
          {
            if($opcion_servicio != 0 && $opcion_etiqueta != 0)
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
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                          ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_servicio != 0)
            {
              $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->whereNull('servicio_oportunidad.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                          ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_etiqueta != 0)
            {
              $remitentes = DB::table('prospectos')
                          ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                          ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                          ->whereNull('prospectos.deleted_at')
                          ->whereNull('oportunidad_prospecto.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->whereNull('etiquetas_oportunidades.deleted_at')
                          ->where('status_oportunidad.id_cat_status_oportunidad',$opcion_estatus)
                          ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                          ->select('prospectos.correo','prospectos.nombre','prospectos.id_prospecto')->distinct()->get();  
            }
          }
        } else {
          if($opcion_servicio == 0 && $opcion_etiqueta == 0)
          {
            $remitentes = DB::table('prospectos')
              ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
              ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
              ->whereNull('prospectos.deleted_at')
              ->whereNull('oportunidad_prospecto.deleted_at')
              ->whereNull('status_oportunidad.deleted_at')
              ->select('prospectos.correo','prospectos.nombre')->distinct()->get();
          }
          else
          {
            if($opcion_servicio != 0 && $opcion_etiqueta != 0)
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
                ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_servicio != 0)
            {
              $remitentes = DB::table('prospectos')
                ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                ->whereNull('prospectos.deleted_at')
                ->whereNull('oportunidad_prospecto.deleted_at')
                ->whereNull('status_oportunidad.deleted_at')
                ->whereNull('servicio_oportunidad.deleted_at')
                ->where('servicio_oportunidad.id_servicio_cat',$opcion_servicio)
                ->select('prospectos.correo','prospectos.nombre')->distinct()->get(); 
            }
            elseif($opcion_etiqueta != 0)
            {
              $remitentes = DB::table('prospectos')
                ->join('oportunidad_prospecto','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                ->join('etiquetas_oportunidades','etiquetas_oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                ->whereNull('prospectos.deleted_at')
                ->whereNull('oportunidad_prospecto.deleted_at')
                ->whereNull('status_oportunidad.deleted_at')
                ->whereNull('etiquetas_oportunidades.deleted_at')
                ->where('etiquetas_oportunidades.id_etiqueta',$opcion_etiqueta)
                ->select('prospectos.correo','prospectos.nombre','prospectos.id_prospecto')->distinct()->get();  
            }
          }
        }
        $count = count($remitentes);

        return response()->json(['remitentes'=>$count]);

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

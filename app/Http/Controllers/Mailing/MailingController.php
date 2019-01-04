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

        $detalle = DetalleMailings::where('id_detalle',$mailing->id_detalle)->first();

        $datosMail['contenido'] = $request->descripcion;
        $datosMail['asunto'] = $request->titulo;
        $datosMail['email'] = ['sergio@concepthaus.mx' => ['name'=>'Sergio'],'paola@concepthaus.mx'=> ['name'=>'Paola'],'javier@concepthaus.mx'=> ['name'=>'Javier'],'liz@concepthaus.mx'=> ['name'=>'Liz'],'sergirams@gmail.com'=> ['name'=>'Sergio']];
        $datosMail['color'] = $request->color;
        $datosMail['color_fuente'] = $detalle->color_fuente;
        $datosMail['subtitulo'] = $detalle->subtitle;
        $datosMail['cta_nombre'] = $detalle->cta_nombre;
        $datosMail['color_cta'] = $detalle->color_cta;

        Mailgun::send('mailing.template_one', $datosMail, function($message) use ($datosMail){
            // $message->to($datosMail['email']);
            $message->to('javier@concepthaus.mx','Javier');
            $message->subject($datosMail['asunto']);
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

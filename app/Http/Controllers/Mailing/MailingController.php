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
        $mailing->preview_text = $request->subtitulo;
        $mailing->text_body = $request->descripcion;
        $mailing->cta_nombre = $request->nombre_boton;
        $mailing->cta_url = $request->url;
        $mailing->color = $request->color;
        $campana->detalle()->save($mailing);
        DB::commit();

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

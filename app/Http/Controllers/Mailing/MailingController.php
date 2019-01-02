<?php

namespace App\Http\Controllers\Mailing;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Modelos\Mailing\Mailings;
use App\Modelos\Mailing\DetalleMailings;
use App\Modelos\Mailing\ImagesMailings;


class MailingController extends Controller
{
    public function addNew(Request $request){

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

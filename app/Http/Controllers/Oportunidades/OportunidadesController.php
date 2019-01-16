<?php

namespace App\Http\Controllers\Oportunidades;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadFile;


use App\Modelos\User;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\DetalleOportunidad;
use App\Modelos\Oportunidad\EtiquetasOportunidad;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ServicioOportunidad;
use App\Modelos\Oportunidad\ProspectoOportunidad;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Modelos\Extras\RecordatorioOportunidad;
use App\Modelos\Extras\DetalleRecordatorioOportunidad;
use App\Modelos\Extras\Evento;
use App\Modelos\Extras\DetalleEvento;
use App\Modelos\Oportunidad\ArchivosOportunidadColaborador;

use DB;
use Mail;


class OportunidadesController extends Controller
{


    public function getAllOportunidades(){
        $oportunidades_total = DB::table('oportunidades')->whereNull('deleted_at')->count();

        $oportunidades_cotizadas = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $oportunidades_cerradas = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_no_viables = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)->count();



         $oportunidades = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('oportunidades.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','cat_status_oportunidad.id_cat_status_oportunidad  as status_id','cat_status_oportunidad.status','cat_status_oportunidad.color','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','users.id as id_colaborador','users.nombre as asigando_nombre','users.apellido as asigando_apellido','oportunidades.created_at')
                            ->orderBy('oportunidades.created_at', 'desc')
                            ->whereNull('oportunidades.deleted_at')
                            ->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total'=>[
                    'valor'=>$oportunidades_total ,
                    'porcentaje'=>100,
                    'color'=>'#4646B9'

                ],
                'cotizadas'=>[
                    'valor'=>$oportunidades_cotizadas,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_cotizadas,$oportunidades_total),
                    'color'=>$this->colorsOportunidades(1)

                ],
                'cerradas'=>[
                    'valor'=>$oportunidades_cerradas,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_cerradas,$oportunidades_total),
                    'color'=>$this->colorsOportunidades(2)

                ],
                'no_viables'=>[
                    'valor'=>$oportunidades_no_viables,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_no_viables,$oportunidades_total),
                    'color'=>$this->colorsOportunidades(3)

                ],
                'oportunidades'=>$oportunidades
            ]
            ],200);
    }

    public function getAllOportunidadesStatus($status){
        $nombre_status = DB::table('cat_status_oportunidad')
                            ->select('cat_status_oportunidad.status as nombre')
                            ->where('cat_status_oportunidad.id_cat_status_oportunidad','=',$status)
                            ->first();

        $total_general = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->count();

        $total = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();

        $fuentes = DB::table('oportunidades')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')->get();

        $oportunidades = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('oportunidades.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','cat_status_oportunidad.status','cat_status_oportunidad.color as color_status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','users.id as id_colaborador','users.nombre as asigando_nombre','users.apellido as asignado_apellido','oportunidades.created_at')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
                            ->orderBy('oportunidades.created_at','desc')
                            ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
           'data'=>[
                'status'=>$nombre_status->nombre,
                'total'=>[
                    'valor'=>$total,
                    'porcentaje'=>intval(round($total*100/$total_general)),
                    'color'=>$this->colorsOportunidades($status)
                ],
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes,$fuentes),
                'oportunidades'=> $oportunidades

            ]
            ],200);
    }

    public function getOneOportunidad($id){
        $oportunidad = Oportunidad::GetOneOportunidad($id);
        if ($oportunidad) {
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>$oportunidad
          ],200);
        }
        return response()->json([
            'message'=>'No se econtro la oportunidad.',
            'error'=>false
        ],200);
    }

    public function updateOneOportunidad(Request $request, $id){

        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador_oportunidad = ColaboradorOportunidad::where('id_oportunidad',$id)->first();
        $oportunidad_detalle = DetalleOportunidad::where('id_oportunidad',$id)->first();
        $prospecto_oportunidad = ProspectoOportunidad::where('id_oportunidad',$id)->first();
        $prospecto = Prospecto::where('id_prospecto',$prospecto_oportunidad->id_prospecto)->first();
        $prospecto_detalle = DetalleProspecto::where('id_prospecto',$prospecto_oportunidad->id_prospecto)->first();

        try{
            DB::beginTransaction();
            $oportunidad->nombre_oportunidad  = $request->nombre_oportunidad;
            $prospecto_oportunidad->id_prospecto = $request->id_prospecto;
            $colaborador_oportunidad->id_colaborador = $request->id_colaborador;

            $oportunidad->save();
            $prospecto_oportunidad->save();
            $colaborador_oportunidad->save();
            DB::commit();

            return response()->json([
                'error'=>false,
                'message'=>'Actualizado Correctamente',
                'data'=>[
                    'oportunidad'=>$oportunidad,
                    'prospecto_oportunidad'=>$prospecto_oportunidad,
                    'colaborador_oportunidad'=>$colaborador_oportunidad,
                ]

            ],200);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'error'=>true,
                'message'=>$e,
            ],400);
        }


        return response()->json([
            'oportunidad'=>$oportunidad,
            'detalle'=>$oportunidad_detalle,
            'prospecto'=>$prospecto,
            'detalle_prospecto'=>$prospecto_detalle
        ],200);
    }

    public function deleteOportunidad($id){
      $auth = $this->guard()->user();
      $oportunidad = Oportunidad::GetOneOportunidad($id);

      if ($oportunidad) {
        try {

          DB::beginTransaction();
          Oportunidad::where('id_oportunidad',$id)->delete();
          DB::commit();
            
          //Historial
            activity('oportunidad')
                ->performedOn($oportunidad)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Eliminó','color'=>'#f42c50'])
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion :subject.nombre_oportunidad </span>');

          return response()->json([
            'error'=>false,
            'message'=>'Oportunidad borrada correctamente.'
          ],200);

        } catch (Exception $e) {

          DB::rollBack();
          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }

      }

      return response()->json([
        'error'=>true,
        'message'=>'Oportunidad no encontrada.'
      ],400);
    }

    public function getEtiquetas($id){
        $oportunidad_etiquetas = Oportunidad::GetOportunidadEtiquetas($id);
        if ($oportunidad_etiquetas) {
          return response()->json([
              'error'=>false,
              'message'=>'Correcto',
              'data'=>$oportunidad_etiquetas
          ],200);
        }
        return response()->json([
            'error'=>true,
            'message'=>'No hay etiquetas.'
        ],400);
    }

    // public function addEtiquetas($request, $id){
    //     $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
    //     $colaborador = $this->guard()->user();
    //     if(isset($request)){
    //         foreach($request as $etiqueta){
    //             $validator = $this->validadorEtiqueta($etiqueta);
    //             if($validator->passes()){
    //                 try{
    //                     DB::beginTransaction();
    //                         $etiqueta_oportunidad = new EtiquetasOportunidad;
    //                         $etiqueta_oportunidad->id_oportunidad = $oportunidad->id_oportunidad;
    //                         $etiqueta_oportunidad->id_etiqueta = $etiqueta['id_etiqueta'];
    //                         $oportunidad->etiquetas_oportunidad()->save($etiqueta_oportunidad);
    //                     DB::commit();
    //
    //                 }catch(Exception $e){
    //                     DB::rollBack();
    //                     return response()->json([
    //                         'error'=>true,
    //                         'message'=>$e,
    //                     ],400);
    //                 }
    //             }
    //             else{
    //                 $errores = $validator->errors()->toArray();
    //                 return response()->json([
    //                     'error'=>true,
    //                     'messages'=>$errores
    //                 ],400);
    //             }
    //         }
    //         return response()->json([
    //                     'error'=>false,
    //                     'message'=>'Registo Correctamente'
    //                 ],200);
    //
    //     }
    //     return response()->json([
    //         'error'=>true,
    //         'messages'=>'No hay etiquetas',
    //         'data'=>$request
    //     ],400);
    //
    // }
    public function addEtiquetas(Request $request, $id){
        //Agregar etiquetas aoportunidad
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();


          try {
            foreach($request->etiquetas as $etiqueta){

                $etiquetas = EtiquetasOportunidad::where('id_oportunidad',$oportunidad->id_oportunidad)->where('id_etiqueta',$etiqueta['id_etiqueta'])->select('id_etiqueta')->get();
                if ($etiquetas->isEmpty()) {
                  DB::beginTransaction();
                    $etiqueta_oportunidad = new EtiquetasOportunidad;
                    $etiqueta_oportunidad->id_oportunidad = $oportunidad->id_oportunidad;
                    $etiqueta_oportunidad->id_etiqueta = $etiqueta['id_etiqueta'];
                    $etiqueta_oportunidad->save();
                  DB::commit();
                }
            }

            return response()->json([
                        'error'=>false,
                        'message'=>'Registro Correcto',
                        'data'=>$oportunidad
                    ],200);

          } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
              'error'=>true,
              'message'=>$e
            ],400);
          }
    }

    public function deleteEtiquetas($id_oportunidad, $id_etiqueta){

      $etiqueta = EtiquetasOportunidad::where('id_oportunidad',$id_oportunidad)->where('id_etiqueta', $id_etiqueta)->first();

      if ($etiqueta) {
        try {
          DB::beginTransaction();
          $etiqueta->delete();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Etiqueta borrada correctamente'
          ],200);

        } catch (Exception $e) {
          DB::rollBack();

          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }

      }

      return response()->json([
        'error'=>false,
        'message'=>'Etiqueta no encontrada.'
      ],200);

    }

    public function getArchivos($id){
        $oportunidad_archivos = Oportunidad::GetOportunidadArchivos($id);
        if ($oportunidad_archivos) {
          foreach($oportunidad_archivos['archivos_oportunidad'] as $archivo){
              $archivo['ext'] = pathinfo($archivo->nombre, PATHINFO_EXTENSION);
          }
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>$oportunidad_archivos
          ],200);
        }

        return response()->json([
            'message'=>'No hay archivos.',
            'error'=>true
        ],400);
    }

    public function addArchivos(Request $request, $id){
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();
        //return response()->json(['archivo'=>$request->hasFile('image'),'id'=>$id],400);
       // if(count($request->files) != 0){

         //   foreach($request->files as $file){
         //       $validator = $this->validadorFile($file);
         //       if($validator->passes()){
             try{

                if($request->file('image')->isValid()){
                            DB::beginTransaction();
                            $archivo_oportunidad = new ArchivosOportunidadColaborador;
                            $archivo_oportunidad->id_oportunidad = $oportunidad->id_oportunidad;
                            $archivo_oportunidad->id_colaborador = $colaborador->id;
                            $archivo_oportunidad->nombre =$request->image->getClientOriginalName();
                            // if(isset($file['desc'])){
                            //     $archivo_oportunidad->desc = $file['desc'];
                            // }
                            $archivo_oportunidad->url = $this->uploadFilesS3($request->image,$colaborador->id,$oportunidad->id_oportunidad);
                            $oportunidad->archivos_oportunidad()->save($archivo_oportunidad);

                            $archivo_oportunidad['ext'] = $request->image->getClientOriginalExtension();

                            DB::commit();
                            return response()->json([
                                'error'=>false,
                                'messages'=>'Archivo registrado',
                                'data'=>$archivo_oportunidad
                            ],200);
                    }
                    return response()->json([
                            'error'=>true,
                            'messages'=>'No existe archivo'
                        ],400);

             }catch(Exception $e){

                return response()->json([
                        'error'=>true,
                        'messages'=>$e
                    ],400);
             }


         //       }else{
                    // $errores = $validator->errors()->toArray();
                    // return response()->json([
                    //     'error'=>true,
                    //     'messages'=>$errores
                    // ],400);
           //     }

          //  }
            // return response()->json([
            //     'error'=>false,
            //     'messages'=>'Succesfully register'
            // ],200);
        //}
        // return response()->json([
        //     'error'=>true,
        //     'messages'=>'No hay archivos'
        // ],400);
    }

    public function deleteArchivos($oportunidad,$id){
        $archivo = ArchivosOportunidadColaborador::where('id_archivos_oportunidad_colaborador',$id)->first();
        if($archivo){
            try{
                DB::beginTransaction();
                $archivo->delete();
                DB::commit();

                return response()->json([
                    'error'=>false,
                    'message'=>'Archivo borrado correctamente.',
                ]);
            }catch(Exception $e){
                return resposonse()->json([
                    'error'=>true,
                    'message'=>$e
                ]);
            }

        }
        return response()->json([
            'error'=>true,
            'message'=>'El archivo no existe.'
        ]);
    }
    public function getEventos($id){
        $oportunidad_eventos = Oportunidad::GetOportunidadEventos($id);

        if ($oportunidad_eventos) {
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>$oportunidad_eventos
          ],200);
        }
        return response()->json([
            'message'=>'No hay eventos para esta oportunidad.',
            'error'=>false
        ],200);

    }

    public function addEventos(Request $request, $id){
        $validator = $this->validadorEvento($request->all());
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();

        if($validator->passes()){
            try{
                DB::beginTransaction();
                $evento->id_colaborador = $colaborador->id;
                $evento->id_oportunidad = $oportunidad->id_oportunidad;
                $evento->save();

                $detalle_evento = new DetalleEvento;
                $detalle_evento->id_evento = $evento->id_evento;
                $detale_evento->fecha_evento = $request->fecha_evento;
                $detalle_evento->hora_evento = $request->hora_evento;
                $detalle_evento->nota_evento = $request->nota_evento;
                $evento->detalle()->save($detalle_evento);

                DB::commit();
                return response()->json([
                    'error'=>false,
                    'message'=>'Evento guardado correctamente.',
                    'data'=>$evento
                ],200);
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e
                ],400);
            }
        }
    }

    public function getRecordatorios($id){
        $oportunidad_recordatorios = Oportunidad::GetOportunidadRecordatorios($id);
        if ($oportunidad_recordatorios) {
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>$oportunidad_recordatorios
          ],200);
        }
        return response()->json([
            'message'=>'No hay recodatorios.',
            'error'=>false
        ],200);
    }

    public function addRecordatorios(Request $request, $id){
        $validator = $this->validadorRecordatorio($request->all());
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();

        if($validator->passes()){
            try{
                DB::beginTransaction();
                $recordatorio = new RecordatorioOportunidad;
                $recordatorio->id_colaborador = $colaborador->id;
                $recordatorio->id_oportunidad = $oportunidad->id_oportunidad;
                $recordatorio->save();

                $detalle_recordatorio = new DetalleRecordatorioOportunidad;
                $detalle_recordatorio->id_recordatorio_oportunidad = $recordatorio->id_recordatorio_oportunidad;
                $detalle_recordatorio->fecha_recordatorio = $request->fecha_recordatorio;
                $detalle_recordatorio->hora_recordatorio = $request->hora_recordatorio;
                $detalle_recordatorio->nota_recordatorio = $request->nota_recordatorio;
                $recordatorio->detalle()->save($detalle_recordatorio);
                DB::commit();

                return response()->json([
                    'error'=>false,
                    'message'=>'Registo Correcto',
                    'data'=>$recordatorio,
                ],200);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e
                ],400);
            }
        }

        $errores = $validator->errors()->toArray();
        return response()->json([
            'error'=>true,
            'messages'=>$errores
        ],400);

    }

    public function addValor(Request $request,$id){


        $detalle = DetalleOportunidad::where('id_oportunidad',$id)->first();

        try{

            DB::beginTransaction();
            if(!$detalle){
              $detalle = new DetalleOportunidad;
              $detalle->id_oportunidad = $id;
            }
            $valor = intval($request->valor);
            $detalle->valor = $valor;
            $detalle->save();
            DB::commit();

            return response()->json([
                'error'=>false,
                'message'=>'Registo Correcto',
                'data'=>$detalle
            ],200);

        }catch(Exception $e){

            return response()->json([
                'error'=>true,
                'messages'=>$e
            ],400);

        }

    }

    public function getServicios($id){
        $servicios_oportunidad = ServicioOportunidad::where('id_oportunidad',$id)->get();
        $servicios = array();

        foreach ($servicios_oportunidad as $servicio){

            $new_object = DB::table('cat_servicios')->where('id_servicio_cat',$servicio->id_servicio_cat)
            ->where('status',1)
            ->select('id_servicio_cat','nombre','descripcion')->first();

            $new_object->id_servicio_oportunidad = $servicio->id_servicio_oportunidad;

            array_push($servicios, $new_object);
        }

        return response()->json([
            'error'=>false,
            'messages'=>'Seleccion Correcta',
            'data'=>$servicios
        ],200);
    }

    public function addServicios(Request $request, $id){
       try{
            DB::beginTransaction();
            $servicio_oportunidad = ServicioOportunidad::where('id_oportunidad',$id)->first();
            $servicio_oportunidad->id_oportunidad = $id;
            $servicio_oportunidad->id_servicio_cat = $request->id_servicio;
            $servicio_oportunidad->save();
            DB::commit();
            return response()->json([
            'error'=>false,
            'messages'=>'Registro Correcto',
            'data'=>$servicio_oportunidad
        ],200);
       }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                    'error'=>true,
                    'message'=>$e
                ],400);
       }


    }
    public function deleteServicios(Request $request, $id){

        $servicio = ServicioOportunidad::where('id_servicio_oportunidad',$request->id_servicio_oportunidad)
                            ->where('id_oportunidad',$id)->first();

        if($servicio){

            if($servicio->delete()){

                    return response()->json([
                    'error'=>false,
                    'messages'=>'Borrado Correctamente',
                    'data'=>$servicio
                ],200);
            }

            return response()->json([
                'error'=>true,
                'messages'=>'Something is wrong.',

            ],400);

        }
        return response()->json([
                'error'=>true,
                'messages'=>'Servicio no encontrado.',

            ],400);


    }
    public function getStatus($id){
        $oportunidad_status = StatusOportunidad::where('id_oportunidad',$id)->first();
        $status = DB::table('cat_status_oportunidad')
                    ->where('id_cat_status_oportunidad',$oportunidad_status->id_cat_status_oportunidad)
                    ->first();

        return response()->json([
            'error'=>false,
            'message'=>'Seleccion Correcta',
            'data'=>$status
        ],200);
    }

    public function updateStatus(Request $request,$id){
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $auth = $this->guard()->user();
        $status = $request->status;
        try{
            DB::beginTransaction();
            $oportunidad_status = StatusOportunidad::where('id_oportunidad',$id)->first();
            $oportunidad_status->id_cat_status_oportunidad = $status;
            $oportunidad_status->save();
            DB::commit();

            activity('oportunidad')
                ->performedOn($oportunidad)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Cambió','color'=>'#7ac5ff'])
                ->useLog('colaborador')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> Cambió de status :subject.nombre_oportunidad </span>');
                
            return response()->json([
                'error'=>false,
                'message'=>'Registro Correcto',
                'data'=>$oportunidad_status
            ],200);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'error'=>true,
                'messages'=>$e
            ],400);

        }

    }




    //AUX
    public function validadorOportunidad(aray $data){
        return Validator::make($data,[
            'nombre_oportunidad'=>'required|string|max:255',
            'id_servicio_cat'=>'required|numeric',
            'id_colaborador'=>'required|string|max:255'
        ]);
    }

    public function validadorEvento(array $data){
        return Validator::make($data,[
            'fecha_evento'=>'required|string|max:255',
            'hora_evento'=>'required|string|max:255',
            'nota_evento'=>'required|string|max:255'
        ]);
    }

    public function validadorRecordatorio(array $data){
        return Validator::make($data,[
            'fecha_recordatorio'=>'required|string|max:255',
            'hora_recordatorio'=>'required|string|max:255',
            'nota_recordatorio'=>'required|string|max:255'
        ]);
    }

    public function validadorEtiqueta(array $data){
        return Validator::make($data,[
            'id_etiqueta'=>'required|numeric'
        ]);
    }

    public function validadorFile(array $data){
        return Validator::make($data,[
            'nombre'=>'required|string',
            'file'=>'required|file'
        ]);
    }
    public function uploadFilesS3($file, $colaborador, $oportunidad){
        $disk = Storage::disk('s3');
        $path = $file->store('oportunidad/'.$colaborador.'/'.$oportunidad,'s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }

    public function guard(){

        return Auth::guard();
    }

    public function colorsOportunidades($id){
        $result = DB::table('cat_status_oportunidad')->select('cat_status_oportunidad.color')->where('id_cat_status_oportunidad',$id)->first();
        return $result->color;
    }

    public function porcentajeOportunidades($oportunidad, $total){
        if($oportunidad == 0){
            return intval($oportunidad);
        }
        return intval(round($oportunidad*100/$total));
    }

    public function FuentesChecker($catalogo, $consulta){
            if(count($catalogo) > count($consulta)){

                if(count($consulta) == 0){

                    foreach($catalogo as $fuente){
                        $fuente->total=0;

                    }
                    return $catalogo;
                }
                else{
                    $collection = collect($consulta);
                    for($i = 0; $i<count($catalogo); $i++){
                        $match = false;
                        for($j=0; $j<count($consulta); $j++){

                            if( $catalogo[$i]->nombre == $consulta[$j]->nombre ){
                                $match = true;
                                break;
                            }
                        }

                        if(!$match){
                            $catalogo[$i]->total = 0;
                            $collection->push($catalogo[$i]);
                        }
                    }
                    return $collection->all();
                }



            }
            return $consulta;

    }

}

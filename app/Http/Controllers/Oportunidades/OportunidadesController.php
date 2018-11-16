<?php

namespace App\Http\Controllers\Oportunidades;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

use DB;
use Mail;


class OportunidadesController extends Controller
{


    public function getAllOportunidades(){
        $oportunidades_total = DB::table('oportunidades')->count();

        $oportunidades_cotizadas = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $oportunidades_cerradas = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_no_viables = DB::table('oportunidades')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)->count();



         $oportunidades = DB::table('oportunidades')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('oportunidades.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad  as status_id','cat_status_oportunidad.status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','prospectos.fuente','users.nombre as asigando_nombre','users.apellido as asigando_apellido','oportunidades.created_at')
                            ->orderBy('oportunidades.created_at', 'desc')
                            ->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total'=>[
                    'valor'=>$oportunidades_total,
                    'porcentaje'=>100,
                    'color'=>'#4646B9'

                ],
                'cotizadas'=>[
                    'valor'=>$oportunidades_cotizadas,
                    'porcentaje'=>intval(round($oportunidades_cotizadas*100/$oportunidades_total)),
                    'color'=>$this->colorsOportunidades(1)

                ],
                'cerradas'=>[
                    'valor'=>$oportunidades_cerradas,
                    'porcentaje'=>intval(round($oportunidades_cerradas*100/$oportunidades_total)),
                    'color'=>$this->colorsOportunidades(2)

                ],
                'no_viables'=>[
                    'valor'=>$oportunidades_no_viables,
                    'porcentaje'=>intval(round($oportunidades_no_viables*100/$oportunidades_total,PHP_ROUND_HALF_DOWN)),
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
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
                            ->select(DB::raw('count(*) as fuente_count, prospectos.fuente'))->groupBy('prospectos.fuente')->get();

        $oportunidades = DB::table('oportunidades')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('oportunidades.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','prospectos.fuente','users.nombre as asigando_nombre','users.apellido as asignado_apellido','oportunidades.created_at')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
                            ->orderBy('oportunidades.created_at','desc')
                            ->get();

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
                'fuentes'=>$fuentes,
                'oportunidades'=> $oportunidades

            ]
            ],200);
    }

    public function getOneOportunidad($id){
        $oportunidad = Oportunidad::GetOneOportunidad($id);
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$oportunidad
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

      $oportunidad = Oportunidad::GetOneOportunidad($id);

      if ($oportunidad) {
        try {

          DB::beginTransaction();
          Oportunidad::where('id_oportunidad',$id)->delete();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Oportunidad borrada correctamente.'
          ],200);

        } catch (Exception $e) {

          DB::rollBack();
          return response()->json([
            'error'=>true,
            'message'=>'Sometring is wrong'.$e
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
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$oportunidad_etiquetas
        ],200);
    }

    public function addEtiquetas(Request $request, $id){
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();
        if(isset($request->etiquetas)){
            foreach($request->etiquetas as $etiqueta){
                $validator = $this->validadorEtiqueta($etiqueta);
                if($validator->passes()){
                    try{
                        DB::beginTransaction();
                            $etiqueta_oportunidad = new EtiquetasOportunidad;
                            $etiqueta_oportunidad->id_oportunidad = $oportunidad->id_oportunidad;
                            $etiqueta_oportunidad->id_etiqueta = $etiqueta['id_etiqueta'];
                            $oportunidad->etiquetas_oportunidad()->save($etiqueta_oportunidad);
                        DB::commit();

                    }catch(Exception $e){
                        DB::rollBack();
                        return response()->json([
                            'error'=>true,
                            'message'=>$e,
                        ],400);
                    }
                }
                else{
                    $errores = $validator->errors()->toArray();
                    return response()->json([
                        'error'=>true,
                        'messages'=>$errores
                    ],400);
                }
            }
            return response()->json([
                        'error'=>false,
                        'message'=>'Registo Correctamente'
                    ],200);

        }
        return response()->json([
            'error'=>true,
            'messages'=>'No hay etiquetas'
        ],400);

    }

    public function getArchivos($id){
        $oportunidad_archivos = Oportunidad::GetOportunidadArchivos($id);
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$oportunidad_archivos
        ],200);
    }

    public function addArchivos(Request $request, $id){
        $oportunidad = Oportunidad::where('id_oportunidad',$id)->first();
        $colaborador = $this->guard()->user();
        if(count($request->files) != 0){

            foreach($request->files as $file){
                $validator = $this->validadorFile($file);
                if($validator->passes()){
                        DB::beginTransaction();
                        $archivo_oportunidad = new ArchivosOportunidadColaborador;
                        $archivo_oportunidad->id_oportunidad = $oportunidad->id_oportunidad;
                        $archivo_oportunidad->id_colaborador = $colaborador->id;
                        $archivo_oportunidad->nombre = $file['nombre'];
                        if(isset($file['desc'])){
                            $archivo_oportunidad->desc = $file['desc'];
                        }
                        $archivo_oportunidad->url = $this->uploadFilesS3($file['file'],$colaborador->id,$prospecto->id_prospecto);
                        $oportunidad->archivos_prospecto_colaborador()->save($archivo_prospecto);
                        DB::commit();


                }else{
                    $errores = $validator->errors()->toArray();
                    return response()->json([
                        'error'=>true,
                        'messages'=>$errores
                    ],400);
                }

            }
            return response()->json([
                'error'=>false,
                'messages'=>'Succesfully register'
            ],200);
        }
        return response()->json([
            'error'=>true,
            'messages'=>'No hay archivos'
        ],400);
    }

    public function getEventos($id){
        $oportunidad_eventos = Oportunidad::GetOportunidadEventos($id);
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$oportunidad_eventos
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
                    'message'=>'Registro Correcto',
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
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$oportunidad_recordatorios
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
            $valor = intval($request->valor);
            DB::beginTransaction();
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
        $status = $request->status;
        try{
            DB::beginTransaction();
            $oportunidad_status = StatusOportunidad::where('id_oportunidad',$id)->first();
            $oportunidad_status->id_cat_status_oportunidad = $status;
            $oportunidad_status->save();
            DB::commit();

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
        $path = $file->store('oportunidad/'.$colaborador,'/'.$prospecto,'s3');
        return $path;
    }

    public function guard(){

        return Auth::guard();
    }

    public function colorsOportunidades($id){
        $result = DB::table('cat_status_oportunidad')->select('cat_status_oportunidad.color')->where('id_cat_status_oportunidad',$id)->first();
        return $result->color;
    }


}

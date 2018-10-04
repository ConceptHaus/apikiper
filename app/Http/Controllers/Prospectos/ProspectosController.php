<?php

namespace App\Http\Controllers\Prospectos;

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
use App\Modelos\Prospecto\EtiquetasProspecto;
use App\Modelos\Prospecto\ArchivosProspectoColaborador;
use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\EtiquetasOportunidad;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ServicioOportunidad;
use App\Modelos\Oportunidad\ProspectoOportunidad;
use App\Evento;
use App\Modelos\Extras\Recordatorio;
use App\Modelos\Extras\DetalleRecordatorio;
use App\Modelos\Extras\DetalleEvento;



use DB;
use Mail;

class ProspectosController extends Controller
{
    public function registerProspecto(Request $request){
        $validator = $this->validadorProspectos($request->all());
        $oportunidades = $request->oportunidades;
        $etiquetas = $request->etiquetas;

        if($validator->passes()){

            try{
                DB::beginTransaction();
                
                $prospecto = new Prospecto;
                $prospectoDetalle = new DetalleProspecto;
                $prospecto->nombre = $request->nombre;
                $prospecto->apellido = $request->apellido;
                $prospecto->correo = $request->correo;
                $prospectoDetalle->nota = $request->nota;
                $prospecto->fuente = $request->fuente;
                $prospecto->save();
                $prospecto->detalle_prospecto()->save($prospectoDetalle);
                if($etiquetas != null){
                    //Crear etiquetas
                    
                    foreach($etiquetas as $etiqueta){
                       $etiqueta_prospecto = new EtiquetasProspecto;
                       $etiqueta_prospecto->id_etiqueta = $etiqueta['id_etiqueta'];
                       $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
                       $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto);
                    
                    }
                }
                if($oportunidades != null){
                    //Crear oportunidades
                    foreach($oportunidades as $oportunidad){
                        
                        //Datos generales oportunidad
                        $nueva_oportunidad = new Oportunidad;
                        $nueva_oportunidad->nombre_oportunidad = $oportunidad['nombre_oportunidad'];
                        $nueva_oportunidad->save();

                        //Servicio de la oportunidad
                        $servicio_oportunidad = new ServicioOportunidad;
                        $servicio_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                        $servicio_oportunidad->id_servicio_cat = $oportunidad['id_servicio_cat'];
                        $nueva_oportunidad->servicio_oportunidad()->save($servicio_oportunidad);
                        
                        //Asignación a colaborador
                        $colaborador_oportunidad = new ColaboradorOportunidad;
                        $colaborador_oportunidad->id_colaborador = $oportunidad['id_colaborador'];
                        $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                        $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);

                        //Asignación a prospecto
                        $prospecto_oportunidad = new ProspectoOportunidad;
                        $prospecto_oportunidad->id_prospecto = $prospecto->id_prospecto;
                        $prospecto_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                        $prospecto_oportunidad->save();
                        
                        if(isset($oportunidad['etiquetas'])){
                            //Etiquetas de oportunidad
                            foreach($oportunidad['etiquetas'] as $etiqueta){
                                $etiqueta_oportunidad = new EtiquetasOportunidad;
                                $etiqueta_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                                $etiqueta_oportunidad->id_etiqueta = $etiqueta['id_etiqueta'];
                                $nueva_oportunidad->etiquetas_oportunidad()->save($etiqueta_oportunidad);
                            }
                            
                        }
                        
                    }

                }
                DB::commit();
                return response()->json([
                        'message'=>'Successfully register',
                        'error'=>false,
                        'data'=>$prospecto,
                    ],200);
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'message'=>$e,
                    'error'=>true,
                    
                ],400);
            }
            
        
        }

        $errores = $validator->errors()->toArray();
        return response()->json([
                'error'=>true,
                'messages'=> $errores
        ],400);
    }

    public function getAllProspectos(){

    }

    public function getOneProspectos($id){

    }

    public function updateProspecto(Request $request, $id){

    }

    public function deleteProspecto($id){
        
    }

    public function getOportunidades($id){

    }

    public function addOportunidades(Request $request, $id){
        $validator = $this->validadorOportunidad($request->all());
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        
        if($validator->passes() || $prospecto == null){
            try{
                DB::beginTransaction();
                //Datos generales oportunidad
                $nueva_oportunidad = new Oportunidad;
                $nueva_oportunidad->nombre_oportunidad = $request->nombre_oportunidad;
                $nueva_oportunidad->save();

                //Servicio de la oportunidad
                $servicio_oportunidad = new ServicioOportunidad;
                $servicio_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $servicio_oportunidad->id_servicio_cat = $request->id_servicio_cat;
                $nueva_oportunidad->servicio_oportunidad()->save($servicio_oportunidad);
                
                //Asignación a colaborador
                $colaborador_oportunidad = new ColaboradorOportunidad;
                $colaborador_oportunidad->id_colaborador = $request->id_colaborador;
                $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);

                //Asignación a prospecto
                $prospecto_oportunidad = new ProspectoOportunidad;
                $prospecto_oportunidad->id_prospecto = $prospecto->id_prospecto;
                $prospecto_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $prospecto_oportunidad->save();
                
                if($request->etiquetas){
                    //Etiquetas de oportunidad
                    foreach($request->etiquetas as $etiqueta){
                        $etiqueta_oportunidad = new EtiquetasOportunidad;
                        $etiqueta_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                        $etiqueta_oportunidad->id_etiqueta = $etiqueta['id_etiqueta'];
                        $nueva_oportunidad->etiquetas_oportunidad()->save($etiqueta_oportunidad);
                    }
                    
                }
                DB::commit();
                return response()->json([
                        'message'=>'Successfully register',
                        'error'=>false,
                        'data'=>$nueva_oportunidad,
                    ],200);
            
            }catch(Exception $e){
                DB::rollBack();
                    return response()->json([
                        'message'=>$e,
                        'error'=>true,
                        
                        
                    ],400);
            }
        }
        $errores = $validator->errors()->toArray();
        return response()->json([
                'error'=>true,
                'messages'=> $errores
        ],400);
        
    }

    public function getRecordatorios($id){


    }

    public function addRecordatorios(Request $request, $id){
        $validator = $this->validadorRecordatorio($request->all());
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();

        if($validator->passes()){
            try{
                DB::beginTransaction();
                $recordatorio = new Recordatorio;
                $recordatorio->id_colaborador = $colaborador->id;
                $recordatorio->id_prospecto = $prospecto->id_prospecto;
                $recordatorio->save();
                $detalle_recordatorio = new DetalleRecordatorio;
                $detalle_recordatorio->id_recordatorio = $recordatorio->id_recordatorio;
                $detalle_recordatorio->fecha_recordatorio = $request->fecha_recordatorio;
                $detalle_recordatorio->hora_recordatorio = $request->hora_recordatorio;
                $detalle_recordatorio->nota_recordatorio = $request->nota_recordatorio;
                $recordatorio->detalle()->save($detalle_recordatorio);
                DB::commit();
                return response()->json([
                        'message'=>'Successfully register',
                        'error'=>false,
                        'data'=>$recordatorio,
                    ],200);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e,
                ],400);
            }
        }

        $errores = $validator->errors()->toArray();
        return response()->json([
            'error'=>true,
            'messages'=>$errores
        ],400);

    }

    public function getEventos($id){

    }

    public function addEventos(Request $request, $id){
        //Agregar un evento por prospecto
        $validator = $this->validadorEvento($request->all());
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();

        if($validator->passes()){
            try{
                DB::beginTransaction();
                $evento  = new Evento;
                $evento->id_colaborador = $colaborador->id;
                $evento->id_prospecto = $prospecto->id_prospecto;
                $evento->save();

                $detalle_evento = new DetalleEvento;
                $detalle_evento->id_evento = $evento->id_evento;
                $detalle_evento->fecha_evento = $request->fecha_evento;
                $detalle_evento->hora_evento = $request->hora_evento;
                $detalle_evento->nota_evento = $request->nota_evento;
                $detalle_evento->lugar_evento = $request->lugar_evento;
                $evento->detalle()->save($detalle_evento);
                
                DB::commit();
                return response()->json([
                        'message'=>'Successfully register',
                        'error'=>false,
                        'data'=>$evento,
                    ],200);
     
            
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e,
                ],400);
            }
        }
        
        $errores = $validator->errors()->toArray();
        return response()->json([
            'error'=>true,
            'messages'=>$errores
        ],400);

    }

    public function getEtiquetas($id){

    }

    public function addEtiquetas(Request $request, $id){
        //Agregar etiquetas al prospecto
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();
        if(isset($request->etiquetas)){
            foreach($request->etiquetas as $etiqueta){
                $validator = $this->validadorEtiqueta($etiqueta);
                if($validator->passes()){
                    try{
                        DB::beginTransaction();
                        $etiqueta_prospecto = new EtiquetasProspecto;
                        $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
                        $etiqueta_prospecto->id_etiqueta = $etiqueta['id_etiqueta'];
                        $prospecto->etiquetas_prospecto()->save($etiqueta_prospecto);
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
                        'message'=>'Successfully register'
                    ],200);

        }
        
        return response()->json([
            'error'=>true,
            'messages'=>'No hay etiquetas'
        ],400);


    }

    public function getArchivos($id){

    }

    public function addArchivos(Request $request, $id){
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();
        if(isset($request->files)){
            foreach($request->files as $file){
                $validator = $this->validadorFile($file);
                if($validator->passes()){
                    try{
                        DB::beginTransaction();
                        $archivo_prospecto = new ArchivosProspectoColaborador;
                        $archivo_prospecto->id_prospecto = $prospecto->id_prospecto;
                        $archivo_prospecto->id_colaborador = $colaborador->id;
                        $archivo_prospecto->nombre = $file['nombre'];
                        if(isset($file['desc'])){
                            $archivo_prospecto->desc = $file['desc'];
                        }
                        $archivo_prospecto->url = $this->uploadFilesS3($file['file'],$colaborador->id,$prospecto->id_prospecto);
                        $prospecto->archivos_prospecto_colaborador()->save($archivo_prospecto);
                        DB::commit();

                    }catch(Exception $e){
                        DB::rollback();
                        return response()->json([
                            'error'=>true,
                            'messages'=>$e
                        ],400);
                    }
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

    //Functiones auxiliares
    public function validadorProspectos(array $data){

        return Validator::make($data,[
            'nombre'=>'required|string|max:255',
            'apellido'=>'required|string|max:255',
            'correo'=>'required|string|email|max:255|unique:prospectos',
            
        ]);

    }

    public function validadorOportunidad(array $data){
        
        return Validator::make($data,[
            'nombre_oportunidad'=>'required|string|max:255',
            'id_servicio_cat'=>'required|numeric',
            'id_colaborador'=>'required|string|max:255'
        ]);

    }

    public function validadorRecordatorio(array $data){
        return Validator::make($data,[
            'fecha_recordatorio'=>'required|string|max:255',
            'hora_recordatorio'=>'required|string|max:255',
            'nota_recordatorio'=>'required|string|max:255'
        ]);
    }

    public function validadorEvento(array $data){
        return Validator::make($data,[
            'fecha_evento'=>'required|string|max:255',
            'hora_evento'=>'required|string|max:255',
            'nota_evento'=>'required|string|max:255',
            'lugar_evento'=>'required|string|max:255'

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

    public function uploadFilesS3($file, $colaborador, $prospecto){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('prospecto/'.$colaborador.'/'.$prospecto,'s3');
        return $path;
    }

    public function guard()
    {
        return Auth::guard();
    }
        

}

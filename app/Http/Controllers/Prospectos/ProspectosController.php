<?php

namespace App\Http\Controllers\Prospectos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\CalendarLinks\Link;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;

use App\Modelos\User;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Prospecto\EtiquetasProspecto;
use App\Modelos\Prospecto\ArchivosProspectoColaborador;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\DetalleOportunidad;
use App\Modelos\Oportunidad\EtiquetasOportunidad;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ServicioOportunidad;
use App\Modelos\Oportunidad\ProspectoOportunidad;
use App\Evento;
use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\DetalleRecordatorioProspecto;
use App\Modelos\Extras\DetalleEvento;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\CatStatusProspecto;


use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;
class ProspectosController extends Controller
{
    public function registerProspecto(Request $request){
        $auth = $this->guard()->user();

        $validator = $this->validadorProspectos($request->all());
        $oportunidades = $request->oportunidades;
        $etiquetas = $request->etiquetas;

        if($validator->passes()){

            try{
                DB::beginTransaction();

                $prospecto = new Prospecto;
                $prospectoDetalle = new DetalleProspecto;
                $statusProspecto = new StatusProspecto;
                $colaborador_prospecto = new ColaboradorProspecto;
                $statusProspecto->id_cat_status_prospecto = 2;
                $prospecto->nombre = $request->nombre;
                $prospecto->apellido = $request->apellido;
                $prospecto->correo = $request->correo;
                $prospectoDetalle->telefono = $request->telefono;
                $prospectoDetalle->celular = intval(preg_replace('/[^0-9]+/', '', $request->celular),10);
                $prospectoDetalle->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
                $prospectoDetalle->puesto = $request->puesto;
                $prospectoDetalle->nota = $request->nota;
                $prospectoDetalle->empresa = $request->empresa;
                $prospecto->fuente = 3;
                $prospecto->save();
                $prospecto->status_prospecto()->save($statusProspecto);
                $prospecto->detalle_prospecto()->save($prospectoDetalle);
                $colaborador_prospecto->id_colaborador = $auth->id;
                $prospecto->colaborador_prospecto()->save($colaborador_prospecto);
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
                        
                        $statusProspecto->id_cat_status_prospecto = 1;
                        $prospecto->status_prospecto()->save($statusProspecto);

                        //Datos generales oportunidad
                        $nueva_oportunidad = new Oportunidad;
                        $statusOportunidad = new StatusOportunidad;
                        $statusOportunidad->id_cat_status_oportunidad = 1;
                        $nueva_oportunidad->nombre_oportunidad = $oportunidad['nombre_oportunidad'];
                        $nueva_oportunidad->save();
                        $nueva_oportunidad->status_oportunidad()->save($statusOportunidad);

                        //Detalle de oportunidades
                        $detalle_oportunidad = new DetalleOportunidad;
                        $detalle_oportunidad->valor = $oportunidad['valor'];
                        $nueva_oportunidad->detalle_oportunidad()->save($detalle_oportunidad);


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
                //Historial
                    activity()
                            ->performedOn($prospecto)
                            ->causedBy($auth)
                            ->withProperties(['accion'=>'Agregó','color'=>'#39ce5f'])
                            ->useLog('prospecto')
                            ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un nuevo prospecto. </span>');

                return response()->json([
                        'message'=>'Registro Correcto',
                        'error'=>false,
                        'data'=>$prospecto,
                    ],200);
            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo crear un prospecto, revísame :("));
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
        $prospectos = Prospecto::GetAllProspectos();
        $prospectos_total = Prospecto::count();
        $prospectos_sin_contactar = Prospecto::join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                    ->where('status_prospecto.id_cat_status_prospecto','=',1)->count();
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'prospectos'=>$prospectos
            ]
        ],200);
    }

    public function getoneprospecto($id){
        $prospecto = Prospecto::GetOneProspecto($id);
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$prospecto
        ],200);
    }

    public function updateProspecto(Request $request, $id){
        $auth = $this->guard()->user();
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $detalle = DetalleProspecto::where('id_prospecto',$id)->first();

        try{

            DB::beginTransaction();
            $prospecto->nombre = $request->nombre;
            $prospecto->apellido = $request->apellido;
            $prospecto->fuente = $request->fuente;
            $prospecto->correo = $request->correo;
            $detalle->telefono = $request->telefono;
            $detalle->celular = $request->celular;
            $detalle->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
            $detalle->nota = $request->nota;
            $detalle->puesto = $request->puesto;
            $detalle->empresa = $request->empresa;
            $prospecto->save();
            $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto', $id)->first();
            if($colaborador_prospecto)
            {
                if($colaborador_prospecto->id_colaborador != $auth->id)
                {
                    $colaborador_prospecto->id_colaborador = $auth->id;
                    $colaborador_prospecto->save();
                }
            }
            else
            {
                $colaborador_prospecto = new ColaboradorProspecto;
                $colaborador_prospecto->id_colaborador = $auth->id;
                $colaborador_prospecto->id_prospecto = $id;
                $colaborador_prospecto->save();
            }
            $detalle->save();
            DB::commit();

            //Historial
            activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Editó','color'=>'#ffcf4c'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion el perfil de un prospecto.</span>');
                             
            return response()->json([
                'error'=>false,
                'message'=>'Actualizado Correctamente',
                'data'=>[
                    'prospecto'=>$prospecto,
                    'detalle'=>$detalle
                ]
            ],200);
        }catch(Exception $e){
            Bugsnag::notifyException(new RuntimeException("No se pudo actualizar un prospecto"));
            return response()->json([
                'error'=>true,
                'message'=>$e,
            ],400);
        }


    }

    public function deleteProspecto($id){
      
      $auth = $this->guard()->user();  
      $prospecto = Prospecto::where('id_prospecto',$id)->first();
      $op = ProspectoOportunidad::where('id_prospecto',$id)->get();
      // return $op;
      if ($prospecto) {

        try{

            DB::beginTransaction();
            foreach ($op as $opor) {
                Oportunidad::where('id_oportunidad',$opor->id_oportunidad)->delete();
            }
            Prospecto::where('id_prospecto', $id)->delete();
            DB::commit();

            //Historial
            activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Eliminó','color'=>'#f42c50'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion a un prospecto.</span>');


            return response()->json([
                'error'=>false,
                'message'=>'Prospecto borrado correctamente.'
            ],200);

        }catch (Exception $e){

          DB::rollback();
          Bugsnag::notifyException(new RuntimeException("No se pudo eliminar un prospecto"));
          return response()->json([
              'error'=>true,
              'message'=>'Something is wrong' .$e
          ],400);
        }
      }

      return response()->json([
          'error'=>true,
          'message'=>'Prospecto no encontrado.'
      ],400);
    }

    public function getProspectosNoContactado (){
      $prospectos_sin_contactar = Prospecto::join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                  ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                                  ->join('cat_fuentes','prospectos.fuente','cat_fuentes.id_fuente')
                                  ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', 'status_prospecto.id_cat_status_prospecto')
                                  ->where('status_prospecto.id_cat_status_prospecto','=',2)
                                  ->whereNull('deleted_at')
                                  ->whereNull('status_prospecto.deleted_at')
                                  ->whereNull('detalle_prospecto.deleted_at')
                                  ->whereNull('cat_fuentes.deleted_at')
                                  ->whereNull('cat_status_prospecto.deleted_at')
                                  ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','cat_fuentes.nombre as fuente','cat_fuentes.id_fuente as id_fuente','cat_fuentes.url as url_fuente','prospectos.deleted_at','prospectos.created_at','prospectos.updated_at','status_prospecto.id_status_prospecto','status_prospecto.id_cat_status_prospecto','detalle_prospecto.whatsapp', 'cat_status_prospecto.color as color')
                                  ->orderBy('status_prospecto.updated_at', 'desc')
                                  ->get();

      return response()->json([
        'error'=>false,
        'message'=>'Correcto',
        'data'=>$prospectos_sin_contactar
      ],200);
    }

    public function getOportunidades($id){
        $prospecto_oportunidades = Prospecto::GetProspectoOportunidades($id);

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$prospecto_oportunidades
        ],200);
    }

    public function addOportunidades(Request $request, $id){
        $auth = $this->guard()->user();
        $validator = $this->validadorOportunidad($request->all());
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $status_prospecto = StatusProspecto::where('id_prospecto',$id)->first();

        if (!$prospecto) {
          return response()->json([
            'error'=>false,
            'message'=>'No se encontro el prospecto.'
          ],200);
        }

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

                //Guarda detalle oportunidad
                $detalle_oportunidad = new DetalleOportunidad;
                $detalle_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $detalle_oportunidad->valor = $request->valor;
                $detalle_oportunidad->save();

                //Cambio de Status Prospecto
                $status_prospecto->id_cat_status_prospecto = 1;
                $status_prospecto->save();

                //Cambio de Status oportunidad
                $id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $status_oportunidad = StatusOportunidad::where('id_oportunidad',$id_oportunidad)->first();
                if (!$status_oportunidad) {
                  $status_oportunidad = new StatusOportunidad;
                  $status_oportunidad->id_oportunidad = $id_oportunidad;
                }
                $status_oportunidad->id_cat_status_oportunidad = 1;
                $status_oportunidad->save();



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

                //Historial
                activity()
                        ->performedOn($nueva_oportunidad)
                        ->causedBy($auth)
                        ->withProperties(['accion'=>'Agregó','color'=>'#39ce5f'])
                        ->useLog('oportunidad')
                        ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion una nueva oportunidad. </span>');
                                
                return response()->json([
                        'message'=>'Oportunidad agregada correctamente.',
                        'error'=>false,
                        'data'=>$nueva_oportunidad,
                    ],200);

            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo crear una oportunidad"));
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
        $prospecto_recordatorios = Prospecto::GetProspectoRecordatorios($id);

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$prospecto_recordatorios
        ],200);
    }

    public function addRecordatorios(Request $request, $id){
        $validator = $this->validadorRecordatorio($request->all());
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();

        if($validator->passes()){
            try{
                DB::beginTransaction();
                $recordatorio = new RecordatorioProspecto;
                $recordatorio->id_colaborador = $colaborador->id;
                $recordatorio->id_prospecto = $prospecto->id_prospecto;
                $recordatorio->save();
                $detalle_recordatorio = new DetalleRecordatorioProspecto;
                $detalle_recordatorio->id_recordatorio_prospecto = $recordatorio->id_recordatorio_prospecto;
                $detalle_recordatorio->fecha_recordatorio = $request->fecha_recordatorio;
                $detalle_recordatorio->hora_recordatorio = $request->hora_recordatorio;
                $detalle_recordatorio->nota_recordatorio = $request->nota_recordatorio;
                $recordatorio->detalle()->save($detalle_recordatorio);
                DB::commit();
                return response()->json([
                        'message'=>'Registro Correcto',
                        'error'=>false,
                        'data'=>$recordatorio,
                    ],200);

            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo crear un recordatorio en prospecto"));
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
        $prospecto_eventos = Prospecto::GetProspectoEventos($id);

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$prospecto_eventos
        ],200);
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

                $link = Link::create('Evento Kiper', $detalle_evento->fecha_evento,$detalle_evento->fecha_evento)
                        ->description($detalle_evento->nota_evento)
                        ->address($request->lugar_evento);

                DB::commit();
                return response()->json([
                        'message'=>'Registro Correcto',
                        'error'=>false,
                        'data'=>$evento,
                        'links'=>['google'=>$link->google(),
                                  'outlook'=>$link->webOutlook(),
                                  'ics'=>$link->ics()]
                    ],200);


            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo crear un evento"));
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
        $prospecto_etiquetas = Prospecto::GetProspectoEtiquetas($id);

        if ($prospecto_etiquetas) {
          return response()->json([
              'message'=>'Etiquetas obtenidas correctamente.',
              'error'=>false,
              'data'=>$prospecto_etiquetas
          ],200);
        }
        return response()->json([
            'message'=>'El prospecto no tiene etiquetas.',
            'error'=>false
        ],200);
    }

    public function addEtiquetas(Request $request, $id){
        //Agregar etiquetas al prospecto
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();

          try {
            foreach($request->etiquetas as $etiqueta){

              $etiquetas = EtiquetasProspecto::where('id_prospecto',$prospecto->id_prospecto)->where('id_etiqueta',$etiqueta['id_etiqueta'])->get();
              if ($etiquetas->isEmpty()) {
                DB::beginTransaction();
                  $etiqueta_prospecto = new EtiquetasProspecto;
                  $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
                  $etiqueta_prospecto->id_etiqueta = $etiqueta['id_etiqueta'];
                  $etiqueta_prospecto->save();
                DB::commit();
              }
            }

            return response()->json([
                        'error'=>false,
                        'message'=>'Registro Correcto',
                        'data'=>$request
                    ],200);

          } catch (Exception $e) {
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo agregar una etiqueta a un prospecto"));
            return response()->json([
              'error'=>true,
              'message'=>$e
            ],400);
          }
    }

    public function deleteEtiquetas($id_etiqueta){

      $etiqueta = EtiquetasProspecto::find($id_etiqueta);

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
          Bugsnag::notifyException(new RuntimeException("No se pudo eliminar una etiqueta de un prospecto"));
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
        $prospecto_archivos = Prospecto::GetProspectoArchivos($id);
        if(count($prospecto_archivos) <= 0)
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>null
        ],200);
        foreach($prospecto_archivos['archivos_prospecto_colaborador'] as $archivo){
            $archivo['ext'] = pathinfo($archivo->nombre, PATHINFO_EXTENSION);
        }
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>$prospecto_archivos
        ],200);
    }

    public function addArchivos(Request $request, $id){
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $colaborador = $this->guard()->user();
        // if(isset($request->files)){
        //     foreach($request->files as $file){
        //         $validator = $this->validadorFile($file);
        //         if($validator->passes()){
                    try{

                        if($request->file('image')->isValid()){
                            DB::beginTransaction();
                            $archivo_prospecto = new ArchivosProspectoColaborador;
                            $archivo_prospecto->id_prospecto = $prospecto->id_prospecto;
                            $archivo_prospecto->id_colaborador = $colaborador->id;
                            $archivo_prospecto->nombre = $request->image->getClientOriginalName();
                            // if(isset($file['desc'])){
                            //     $archivo_prospecto->desc = $file['desc'];
                            // }
                            $archivo_prospecto->url = $this->uploadFilesS3($request->image,$colaborador->id,$prospecto->id_prospecto);
                            $prospecto->archivos_prospecto_colaborador()->save($archivo_prospecto);
                            $archivo_prospecto['ext'] = $request->image->getClientOriginalExtension();
                            DB::commit();
                            return response()->json([
                                'error'=>false,
                                'messages'=>'Archivo registrado',
                                'data'=>$archivo_prospecto
                            ],200);
                        }
                        return response()->json([
                            'error'=>true,
                            'messages'=>'No existe archivo'
                        ],400);


                    }catch(Exception $e){
                        DB::rollback();
                        Bugsnag::notifyException(new RuntimeException("No se pudo agregar un archivo en prospecto"));
                        return response()->json([
                            'error'=>true,
                            'messages'=>$e
                        ],400);
                    }
            

    }

    public function deleteArchivos($prospecto,$id){
            $archivo = ArchivosProspectoColaborador::where('id_archivos_prospecto_colaborador',$id)->first();
            if($archivo){
                try{
                DB::beginTransaction();
                $archivo->delete();
                DB::commit();

                return response()->json([
                    'error'=>false,
                    'message'=>'Successfully deleted',
                ]);

                }catch(Exception $e){
                    Bugsnag::notifyException(new RuntimeException("No se pudo eliminar un archivo en prospecto"));
                    return response()->json([
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


    public function sendMailing($id){
        $oportunidades = DB::table('oportunidades')->whereNull(deleted_at)->get();
        $user = Prospecto::where('id_prospecto',$id)->first();
        $usera['email'] = $user->correo;
        $usera['nombre'] = $user->nombre;


        // Mailgun::send('mailing.template_one', $usera, function ($message) {
        //     $message->tag('myTag');
        //     $message->testmode(true);
        //     $message->to('sergio@concepthaus.mx', 'User One', [
        //         'age' => 37,
        //         'city' => 'New York'
        //     ]);
        // });
        // Mailgun::send('auth.emails.register', $usera, function($message){
        //     $message->to('sergio@concepthaus.mx', 'User One', [
        //         'age' => 37,
        //         'city' => 'New York'
        //     ]);
        //     $message->to('paola@concepthaus.mx', 'User Two', [
        //         'age' => 41,
        //         'city' => 'London'
        //     ]);
        //     $message->to('javier@concepthaus.mx', 'User One', [
        //         'age' => 37,
        //         'city' => 'New York'
        //     ]);
        //     $message->to('isaac@concepthaus.mx', 'User Two', [
        //         'age' => 41,
        //         'city' => 'London'
        //     ]);
        //     $message->to('liz@concepthaus.mx', 'User One', [
        //         'age' => 37,
        //         'city' => 'New York'
        //     ]);
        //     $message->to('sergirams@gmail.com', 'User Two', [
        //         'age' => 41,
        //         'city' => 'London'
        //     ]);
        // });

        return response()->json([
        'user1@example.com' => [
            'name' => 'User One',
            'age' => 37,
            'city' => 'New York'
        ]]);
    }

    public function getStatus(){
        $status = CatStatusProspecto::all();

        return response()->json([
            'error'=>false,
            'data'=>$status
        ]);
    }
    //Functiones auxiliares
    public function validadorProspectos(array $data){

        return Validator::make($data,[
            'nombre'=>'required|string|max:255',
            //'apellido'=>'required|string|max:255',
            'correo'=>'required|string|email|max:255|unique:prospectos',

        ]);

    }

    public function validadorOportunidad(array $data){

        return Validator::make($data,[
            'nombre_oportunidad'=>'required|string|max:255',
            //'id_servicio_cat'=>'required|numeric',
            //'id_colaborador'=>'required|string|max:255'
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
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function addFoto(Request $request, $id){
      // return $request->all();
        $foto_prospecto = FotoProspecto::where('id_prospecto',$id)->first();
        $prospecto = Prospectos::where('id_prospecto',$id)->first();

        try{

            if($request->file('image')->isValid()){
              if (!$foto_prospecto) {
                $foto_prospecto = new FotoProspecto;
              }
                DB::beginTransaction();
                $foto_prospecto->id_prospecto = $prospecto->id_prospecto;
                $foto_prospecto->url_foto = $this->uploadFilesS3($request->image,$prospecto->id_prospecto);
                $prospecto->foto()->save($foto_prospecto);
                $foto_prospecto['ext'] = $request->image->getClientOriginalExtension();
                DB::commit();
                return response()->json([
                    'error'=>false,
                    'messages'=>'Foto actualizada.',
                    'data'=>$foto_prospecto
                ],200);
            }
            return response()->json([
                'error'=>true,
                'messages'=>'No existe foto.'
            ],400);


        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'error'=>true,
                'messages'=>$e
            ],400);
        }
    }

    public function deleteFoto($id){
      $foto_prospecto = FotoColaborador::where('id_prospecto',$id)->first();

      if ($foto_prospecto->isEmpty()) {
        return response()->json([
          'error'=>true,
          'message'=>'Foto no encontrada.'
        ],400);
      }

      try {
        DB::beginTransaction();
        $foto_prospecto->delete();
        DB::commit();

        return response()->json([
          'error'=>false,
          'message'=>'Foto elimiada correctamente.'
        ],200);

      } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
          'error'=>true,
          'message'=>$e
        ],400);
      }

    }

    public function uploadFotoS3($file, $prospecto){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('prospectos/foto_perfil/'.$prospecto,'s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }


}

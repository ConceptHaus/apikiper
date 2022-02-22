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

use App\Modelos\Empresa\Empresa;
use App\Modelos\Empresa\EmpresaProspecto;
use App\Http\Services\Auth\AuthService;

use App\Evento;
use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\DetalleRecordatorioProspecto;
use App\Modelos\Extras\DetalleEvento;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\CatStatusProspecto;
use App\Events\Historial;
use App\Events\Event;
use \App\Http\Enums\Permissions;
use App\Imports\ProspectosImport;
use App\Exports\ProspectosReports;
use Excel;

use App\Http\Enums\OldRole;
use App\Http\Services\Users\UserService;
use App\Http\Services\Roles\RolesService;
use App\Modelos\Role;

use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;
class ProspectosController extends Controller
{

    private $userServ;
    private $roleServ;

    public function __construct(
            UserService $userService,
            RolesService $roleService
        ){
        $this->userServ = $userService;
        $this->roleServ = $roleService;
    }

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
                
                $statusProspecto->id_cat_status_prospecto = 2;
                $prospecto->nombre = $request->nombre;
                $prospecto->apellido = $request->apellido;
                $prospecto->correo = $request->correo;
                $prospectoDetalle->extension = $request->extension;
                $prospectoDetalle->telefono = $request->telefono;
                $prospectoDetalle->celular = intval(preg_replace('/[^0-9]+/', '', $request->celular),10);
                $prospectoDetalle->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
                $prospectoDetalle->puesto = $request->puesto;
                $prospectoDetalle->nota = $request->nota;
                $prospecto->fuente = 3;
                $prospecto->save();
                $prospecto->status_prospecto()->save($statusProspecto);
                
                
                
                // $prospecto->colaborador_prospecto()->save($colaborador_prospecto);
                // $prospecto->save($colaborador_prospecto);

                $colaborador_prospecto = new ColaboradorProspecto;
                
                if( isset($request->colaborador['id'])){
                    $colaborador_prospecto->id_colaborador = $request->colaborador['id'];
                }else{
                    $colaborador_prospecto->id_colaborador = $auth->id;
                }
                $colaborador_prospecto->id_prospecto = $prospecto->id_prospecto;
                
                $colaborador_prospecto->save();
                


                
                if(!$request->hsh){
                    if( isset($request->empresa))
                    {
                        $empresa = Empresa::where('nombre', '=', $request->empresa)->wherenull('deleted_at')->first();
                        if($empresa){
                            $prospecto_empresa = new EmpresaProspecto;
                            $prospecto_empresa->id_empresa = $empresa->id_empresa;
                            $prospecto_empresa->id_prospecto = $prospecto->id_prospecto;
                            $prospecto_empresa->save();
                            $prospectoDetalle->empresa = $request->empresa;
                        }else{
                            $empresa_new = new Empresa;
                            $empresa_new->nombre = $request->empresa;
                            //$empresa_new->save();
                            
                            if($empresa_new->save()){
                                $prospecto_empresa = new EmpresaProspecto;
                                $prospecto_empresa->id_empresa = $empresa_new->id_empresa;
                                $prospecto_empresa->id_prospecto = $prospecto->id_prospecto;
                                $prospecto_empresa->save();
                            }
                            $prospectoDetalle->empresa = $request->empresa;
                            
                        }
                        
                    }
                }else {
                    $prospectoDetalle->empresa = $request->empresa;
                }
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
                        if(isset($oportunidad['valor'])){
                            $valor = str_replace('$ ', '',$oportunidad['valor']);
                            $valor = str_replace(',', '', $valor);
                            $detalle_oportunidad->valor = $valor;
                        }
                        else{
                            $detalle_oportunidad->valor = 0;
                        }
                        if(isset($oportunidad['meses']))
                            $detalle_oportunidad->meses = $oportunidad['meses'];
                        $nueva_oportunidad->detalle_oportunidad()->save($detalle_oportunidad);


                        //Servicio de la oportunidad


                            $servicio_oportunidad = new ServicioOportunidad;
                            $servicio_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                            $servicio_oportunidad->id_servicio_cat = $oportunidad['id_servicio_cat'];
                            $nueva_oportunidad->servicio_oportunidad()->save($servicio_oportunidad);


                        //Asignación a colaborador

                            if(count($oportunidad['id_colaborador']) > 0){
                                foreach($oportunidad['id_colaborador'] as $col_op){
                                    $colaborador_oportunidad = new ColaboradorOportunidad;
                                    $colaborador_oportunidad->id_colaborador = $col_op;
                                    $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                                    $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);
                                }
                            }else{
                                $colaborador_oportunidad = new ColaboradorOportunidad;
                                $colaborador_oportunidad->id_colaborador = $auth->id;
                                $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                                $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);    
                            }
                            
                            



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
                    $actividad = activity()
                            ->performedOn($prospecto)
                            ->causedBy($auth)
                            ->withProperties(['accion'=>'Agregó','color'=>'#39ce5f'])
                            ->useLog('prospecto')
                            ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un nuevo prospecto. </span>');
                    event( new Historial($actividad));
                    
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

        $errores_msg = array();

        if (!empty($errores)) {
            foreach ($errores as $key => $error_m) {
                $errores_msg[] = $error_m[0];
                break;
            }
        }

        return response()->json([
                'error'=>true,
                'message'=> $errores_msg
        ],400);
    }

    public function importProspectos(Request $request){


        try{
            Excel::import(new ProspectosImport, request()->file('import'));

            return response()->json([
                'message'=>'Success',
                'error'=>false,
                'data'=>[
                    'prospectos'=>'Los prospectos se han guardado correctamente.'
                ]
                ],200);
        }
        
        catch(Exception $e){
            return response()->json([
                'message'=>$e,
                'error'=>true
            ],400);
        }
        
    }

    public function getAllProspectos(){

        $permisos = User::getAuthenticatedUserPermissions();
        $auth = $this->guard()->user();
        if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
            $prospectos = Prospecto::GetAllProspectos();
            $prospectos_total = Prospecto::count();
            $prospectos_sin_contactar = Prospecto::join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                    ->where('status_prospecto.id_cat_status_prospecto','=',1)->count(); 
        }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
            $prospectos = Prospecto::join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                    ->where('colaborador_prospecto.id_colaborador',$auth->id)->get();
            $prospectos_total = Prospecto::join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                        ->where('colaborador_prospecto.id_colaborador',$auth->id)->count();
            $prospectos_sin_contactar = Prospecto::join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                                ->where('status_prospecto.id_cat_status_prospecto','=',1)->count();
        }
        else{
            $prospectos                 = [];
            $prospectos_total           = [];
            $prospectos_sin_contactar   = [];    
        }
        
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
        // return $request->all();
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
            if(!$request->hsh){
                if(isset($request->empresa)){
                    echo "empresa : ".$request->empresa;

                    $prospecto_empresa = EmpresaProspecto::where('id_prospecto', '=', $id);
                    if( $prospecto_empresa ) {
                        echo "Hola";
                        $prospecto_empresa->delete();
                    }

                    return response()->json([
                        'error'=>false,
                        'messages'=> "prospectos_empresa:    request empresa:".$request->empresa
                    ],200);

                    exit;
                    
                    if( Empresa::where('nombre','=',$request->empresa)->first() != null ){
                        $empresa = Empresa::where('nombre','=',$request->empresa)->first();
                    }else{
                        $empresa = new Empresa;
                        $empresa->nombre = $request->empresa;
                        $empresa->save();
                    }
                    
                    
                    $prospecto_empresa = new EmpresaProspecto;
                    $prospecto_empresa->id_empresa = $empresa->id_empresa;
                    $prospecto_empresa->id_prospecto = $id;
                    $prospecto_empresa->save();
                    

                    /*$empresa = Empresa::where('nombre', '=', $request->empresa)->wherenull('deleted_at')->first();
                    
                    if($empresa){
                        $empresa_prospecto = EmpresaProspecto::where('id_prospecto', '=', $prospecto->id_prospecto)
                                            ->wherenull('deleted_at')
                                            ->first();
                        if($empresa_prospecto){
                            $prospecto_empresa = EmpresaProspecto::find($empresa_prospecto->id_prospecto_empresa);
                            $prospecto_empresa->id_empresa = $empresa->id_empresa;
                            $prospecto_empresa->save();
                        } else {
                            DB::connection()->enableQueryLog();

                            // se crea la relacion empresa prospecto de una empresa existente
                            $prospecto_empresa = new EmpresaProspecto;
                            $prospecto_empresa->id_empresa = $empresa->id_empresa;
                            $prospecto_empresa->id_prospecto = $prospecto->id_prospecto;
                            $prospecto_empresa->save();
                            $queries = \DB::getQueryLog();

                            print_r($queries);
                            dd($queries);
                            

                            
                            
                        }
                    }else{
                        echo "else 2";
                        $empresa = new Empresa;
                        $empresa->nombre = $request->empresa;
                        $empresa->save();

                        $prospecto_empresa = EmpresaProspecto::where('id_prospecto', '=', $prospecto->id_prospecto)
                                            ->wherenull('deleted_at')
                                            ->first();
                        $prospecto_empresa->id_empresa = $empresa->id_empresa;
                        $prospecto_empresa->save();
                    }*/
                    return response()->json([
                        'error'=>false,
                        'messages'=> "id prospecto:".$prospecto->id_prospecto."    id empresa:".$empresa->id_empresa
                    ],200);
                }
            }else {
                $detalle->empresa = $request->empresa;
            }
            $detalle->save();            
            DB::commit();

            //Historial
            $actividad = activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Editó','color'=>'#ffcf4c'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion el perfil de un prospecto.</span>');
            event( new Historial($actividad));
                          
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
            $borrar = Prospecto::where('id_prospecto', $id)->first();
            $borrar->correo = null;
            $borrar->save();
            $borrar->delete();
            $prospecto_empresa = EmpresaProspecto::where('id_prospecto', '=', $id)->wherenull('deleted_at')->get();
            foreach($prospecto_empresa as $pe){
                $pe->delete();
            }
            DB::commit();

            //Historial
            $actividad = activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Eliminó','color'=>'#f42c50'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion a un prospecto.</span>');

            event( new Historial($actividad));

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

        if($validator->passes() || $prospecto !== null){
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
                // $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto',$id)->first();
                // $colaborador = $colaborador_prospecto->id_colaborador ?? $auth->id;
               
                $colaborador_prospecto_id = (!is_null($request->id_colaborador)) ? $request->id_colaborador : $auth->id;
                
                $colaborador_oportunidad = new ColaboradorOportunidad;
                $colaborador_oportunidad->id_colaborador = $colaborador_prospecto_id;
                $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);

                // foreach($colaboradores as $_colaborador){
                //     $colaborador_oportunidad = new ColaboradorOportunidad;
                //     $colaborador_oportunidad->id_colaborador = $_colaborador;
                //     $colaborador_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                //     $nueva_oportunidad->colaborador_oportunidad()->save($colaborador_oportunidad);
                // }

                //Asignación a prospecto
                $prospecto_oportunidad = new ProspectoOportunidad;
                $prospecto_oportunidad->id_prospecto = $prospecto->id_prospecto;
                $prospecto_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $prospecto_oportunidad->save();

                //Guarda detalle oportunidad
                $detalle_oportunidad = new DetalleOportunidad;
                $detalle_oportunidad->id_oportunidad = $nueva_oportunidad->id_oportunidad;
                $valor = str_replace('$ ', '', $request->valor);
                $valor = str_replace(',', '', $valor);
                $detalle_oportunidad->valor = $valor;
                $detalle_oportunidad->meses = $request->meses;
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
                $actividad = activity()
                        ->performedOn($nueva_oportunidad)
                        ->causedBy($auth)
                        ->withProperties(['accion'=>'Agregó','color'=>'#39ce5f'])
                        ->useLog('oportunidad')
                        ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion una nueva oportunidad. </span>');
                
                event( new Historial($actividad));
                             
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

    public function getRecordatoriosAsHTML($id){
        // setlocale(LC_TIME, 'es_ES.UTF-8');
        setlocale(LC_TIME, 'en_EN.UTF-8');
        
        $prospecto_recordatorios = Prospecto::GetProspectoRecordatorios($id);
        $recordatorios = "";

        if(isset($prospecto_recordatorios['recordatorios']) AND count($prospecto_recordatorios['recordatorios']) > 0){
            $recordatorios = $recordatorios . '<div class="scroller" data-height="280px">';

            foreach ($prospecto_recordatorios['recordatorios'] as $key => $prospecto_recordatorio) {
                $recordatorios = $recordatorios . '<div class="time-line-notas"><div class="d-flex justify-content-between">';
                $recordatorios = $recordatorios . '<span>'.strftime("%d de %B de %Y", strtotime($prospecto_recordatorio->detalle['fecha_recordatorio'])).'</span>';
                $recordatorios = $recordatorios . '<span>'.date( 'H:i', strtotime($prospecto_recordatorio->detalle['fecha_recordatorio'])).'</span></div>';
                $recordatorios = $recordatorios . '<div class="notas-textos"><p>'.$prospecto_recordatorio->detalle['nota_recordatorio'].'</p></div>';
            }

            $recordatorios = $recordatorios . '</div>';

            $recordatorios = str_replace('January de', 'Enero de', $recordatorios);
            $recordatorios = str_replace('February de', 'Febrero de', $recordatorios);
            $recordatorios = str_replace('March de', 'Marzo de', $recordatorios);
            $recordatorios = str_replace('April de', 'Abril de', $recordatorios);
            $recordatorios = str_replace('May de', 'Mayo de', $recordatorios);
            $recordatorios = str_replace('June de', 'Junio de', $recordatorios);
            $recordatorios = str_replace('July de', 'Julio de', $recordatorios);
            $recordatorios = str_replace('August de ', 'Agosto de', $recordatorios);
            $recordatorios = str_replace('September de', 'Septiembre de', $recordatorios);
            $recordatorios = str_replace('October de', 'Octubre de', $recordatorios);
            $recordatorios = str_replace('November de', 'Noviembre de', $recordatorios);
            $recordatorios = str_replace('December de', 'Diciembre de', $recordatorios);
        }else{
            $recordatorios = '<p class="list-group-item font-400 text-muted text-center">No hay recordatorios</p>';
        }
        return response()->json([
            'message'   =>  mb_convert_encoding($recordatorios, 'UTF-8', 'UTF-8'),
            'error'     => false,
            'data'      => $prospecto_recordatorios
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
        if($prospecto_archivos == [])
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

    public function downloadProspectos($role_id, $rol, $id_user, $correos, $nombre, $telefono, $status, $grupo, $etiquetas, $fechaInicio, $fechaFin, $colaboradores, $busqueda){
        $correos = json_decode($correos);
        $nombre = json_decode($nombre);
        $telefono = json_decode($telefono);
        $status = json_decode($status);
        $fuente = json_decode($grupo);
        $etiqueta = json_decode($etiquetas);
        $fechaInicio = json_decode($fechaInicio);
        $fechaFin = json_decode($fechaFin);
        $colaboradores = json_decode($colaboradores);
        $busqueda = json_decode($busqueda);

        $usuario = $this->userServ->findById($id_user);
        $roles = $this->roleServ->findById($usuario->role_id);

        $permisos = json_decode($roles->acciones);
      
        
        $date = Carbon::now();
        $headings = [
            'Asesor',
            'Fecha',
            'Status',
            'Cómo se enteró',
            'Cliente',
            'Teléfono',
            'Mail',
            'Comentarios',
            'Seguimiento',
            'Etiquetas',
            'Empresa'
        ];

        if($rol == OldRole::POLANCO){
            $desarrollo='polanco';

        }else if($rol == OldRole::NAPOLES){
            $desarrollo='napoles';

        }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
            $desarrollo = 'all';

        }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
            $desarrollo = 'user';
            
        } else {
            return response()->json([
                'message'=>'No tienes permiso',
                'error'=>true],401);
        }
        return (new ProspectosReports($headings,$desarrollo,$id_user, $correos, $nombre, $telefono, $status, $fuente, $etiqueta, $fechaInicio, $fechaFin, $colaboradores, $busqueda))->download("{$date}_{$desarrollo}_reporte.xlsx");
    }

    //Functiones auxiliares
    public function validadorProspectos(array $data){

        return Validator::make($data,[
            'nombre'    => 'nullable|string|max:30',
            'apellido'  => 'nullable|string|max:30',
            // 'correo'    => 'required|email|max:50|unique:prospectos,correo',
            'telefono'  => 'required|max:9999999999',
            'celular'   => 'max:9999999999',
            'extension' => 'max:999999',
            'empresa'   => 'nullable|string|max:50',
            'puesto'    => 'nullable|string|max:35',
            'nota'      => 'nullable|string|max:250',


        ]);

    }

    public function validadorOportunidad(array $data){

        return Validator::make($data,[
            'nombre_oportunidad'=>'required|string|max:40',
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

<?php
namespace App\Http\Controllers\Colaboradores;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;
use App\Modelos\Colaborador\FotoColaborador;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ArchivosOportunidadColaborador;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Prospecto\ArchivosProspectoColaborador;
use App\Modelos\Extras\RecordatorioColaborador;
use App\Modelos\Oportunidad\CatStatusOportunidad;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use App\Events\Historial;
use App\Events\Event;
use App\Http\Services\Colaboradores\ColaboradoresService;

use DB;
use Mail;
use Mailgun;

class ColaboradoresController extends Controller
{
    
    protected function validatorDelete(array $data)
    {
        return Validator::make($data, [
            'id_borrar' => 'required|exists:users,id',
            'id_asignar'=> 'required|exists:users,id'
        ]);
    }

    public function registerColaborador(Request $request){
            $validator = ColaboradoresService:: validator($request->all());
            $auth = $this->guard()->user();
            
            if($validator->passes()){
                $new_colaborador    = array('nombre'            => $request->nombre,
                                            'apellido'          => $request->apellido,
                                            'email'             => $request->email,
                                            'role_id'           => $request->role_id,
                                            'puesto'            => $request->puesto,
                                            'telefono'          => $request->telefono,
                                            'celular'           => $request->celular,
                                            'fecha_nacimiento'  => $request->fecha_nacimiento );

                $colaborador = ColaboradoresService::createColaborador($new_colaborador, $auth);

                if(!$colaborador['error']){
                    
                    return response()->json([
                                'message'   => $colaborador['message'],
                                'error'     => $colaborador['error'],
                                'data'      => $colaborador['data']
                            ],200);
                }else{
                    return response()->json([
                                'message'   => $colaborador['message'],
                                'error'     => $colaborador['error'],
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
                'messages'=> $errores_msg
            ],400);
    }

    public function getAllColaboradores(){
        $colaboradores = User::GetUsersWithVisibleRole();

        if ($colaboradores) {
          return response()->json([
              'message'=>'Colaboradores obtenidos correctamente.',
              'error'=>false,
              'data'=>$colaboradores
          ],200);
        }
        return response()->json([
            'message'=>'No se encontraron colaboradores.',
            'error'=>false
        ],200);
    }

    public function getOneColaborador($id){
      $id_user = $id;

      $user = User::getOneUser($id_user);

      $oportunidades = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->whereNull('oportunidades.deleted_at')
                          ->whereNull('colaborador_oportunidad.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_1 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->whereNull('oportunidades.deleted_at')
                          ->whereNull('colaborador_oportunidad.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',1)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_2 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->whereNull('oportunidades.deleted_at')
                          ->whereNull('colaborador_oportunidad.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',2)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_3 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->whereNull('oportunidades.deleted_at')
                          ->whereNull('colaborador_oportunidad.deleted_at')
                          ->whereNull('status_oportunidad.deleted_at')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',3)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

       $status_genericos = DB::table('oportunidades')
                                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                    ->whereNull('oportunidades.deleted_at')
                                    ->where('colaborador_oportunidad.id_colaborador',$id_user)
                                    ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                                    ->get();
        
        $catalogo_status = CatStatusOportunidad::all();

        $recordatorios = DB::table('recordatorios_prospecto')
                      ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                      ->where('recordatorios_prospecto.id_colaborador',$id_user)
                      ->whereNull('recordatorios_prospecto.deleted_at')
                      ->whereNull('detalle_recordatorio_prospecto.deleted_at')
                      ->orderBy('detalle_recordatorio_prospecto.fecha_recordatorio', 'desc')
                      ->get();

      $detalle = DetalleColaborador::where('id_colaborador',$id_user)
                      ->first();
                      
      $img = FotoColaborador::where('id_colaborador', $id_user)
                      ->select('url_foto')
                      ->first();


      return response()->json([
          'user'=>$user,
          'detalle'=>$detalle,
          'img_perfil'=>$img,
          'status'=>$this->StatusChecker($catalogo_status,$status_genericos),
          'oportunidades'=>[
              'status_1'=>$this->statusEmpty($status_1,1),
              'status_2'=>$this->statusEmpty($status_2,2),
              'status_3'=>$this->statusEmpty($status_3,3)
          ],
          'recordatorios'=>$recordatorios,
          'activity'=>$user->actions
      ],200);
    }

    public function statusEmpty($status,$id){
        if(count($status) == 0){

            $empty = DB::table('cat_status_oportunidad')
                    ->select('id_cat_status_oportunidad','status','color')
                    ->where('id_cat_status_oportunidad',$id)
                    ->get();
            return $empty;

        }else{
            return $status;
        }
    }

    public function updateColaborador(Request $request)
    {
        $auth       = $this->guard()->user();
        $validator  = ColaboradoresService::validatorUpdate($request->all());

        if($validator->passes()){

            $id_colaborador     = $request->id;
            $colaborador        = ColaboradoresService::getColaborador($id_colaborador);
            $colaborador_ext    = ColaboradoresService::getColaboradorExt($id_colaborador);
            $colaborador_info   = array('id'                => $request->id,
                                        'nombre'            => $request->nombre,
                                        'apellido'          => $request->apellido,
                                        'role_id'           => $request->role_id,
                                        'puesto'            => $request->puesto,
                                        'telefono'          => $request->telefono,
                                        'celular'           => $request->celular,
                                        'fecha_nacimiento'  => $request->fecha_nacimiento );
            $update_colaborador = ColaboradoresService::updateColaborador($colaborador_info, $colaborador,  $colaborador_ext, $auth);

            if(!$update_colaborador['error']){
                return response()->json([
                    'message'   =>'Correcto',
                    'error'     =>$update_colaborador['error'],
                    'data'      =>$update_colaborador['data']
                ]);
            }else{
                return response()->json([
                    'message'   =>$update_colaborador['message'],
                    'error'     =>$update_colaborador['error']
                ],400);
            }
        }

        $errores = $validator->errors()->toArray();

        return response()->json([
            'error'=>true,
            'messages'=> $errores
        ],400);
    }

    public function deleteColaborador(Request $request){
      $auth = $this->guard()->user();
      $validator = $this->validatorDelete($request->all());

      $id_borrar = $request->id_borrar;

      $id_asignar = $request->id_asignar;


      $colaborador_borrar = User::where('id',$id_borrar)->get();
      $colaborador_asignar = User::where('id',$id_asignar)->get();

      if ($validator->passes()) {
        try{

          DB::beginTransaction();

          $oportunidades = ColaboradorOportunidad::where('id_colaborador',$id_borrar)->get();
          foreach ($oportunidades as $oportunidad) {
            $oportunidad->id_colaborador = $id_asignar;
            $oportunidad->save();
          }

          $prospectos = ColaboradorProspecto::where('id_colaborador',$id_borrar)->get();
          foreach ($prospectos as $prospecto) {
            $prospecto->id_colaborador = $id_asignar;
            $prospecto->save();
          }

          $archivos_prospecto = ArchivosProspectoColaborador::where('id_colaborador',$id_borrar)->get();
          foreach ($archivos_prospecto as $archivo_prospecto) {
            $archivo_prospecto->id_colaborador = $id_asignar;
            $archivo_prospecto->save();
          }

          $archivos_oportunidad = ArchivosOportunidadColaborador::where('id_colaborador',$id_borrar)->get();
          foreach ($archivos_oportunidad as $archivo_oportunidad) {
            $archivo_oportunidad->id_colaborador = $id_asignar;
            $archivo_oportunidad->save();
          }

          $borrar = User::where('id', $id_borrar)->first();
          //$borrar->email = null;
          //$borrar->save();
          $borrar->delete();

          DB::commit();
            //Historial
                $actividad = activity()
                    ->performedOn($borrar)
                    ->causedBy($auth)
                    ->withProperties(['accion'=>'Eliminó','color'=>'#f42c50'])
                    ->useLog('colaborador')
                    ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion a un colaborador.</span>');
                
                event( new Historial($actividad));

          return response()->json([
              'message'=>'Borrado Correctamente',
              'error'=>false,
              'data'=>$id_borrar
          ],200);


        }catch (Exception $e){

          DB::rollBack();
          Bugsnag::notifyException(new RuntimeException("El usuario no borrar a un colaborador"));   
          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);

        }
      }

      $errores = $validator->errors()->toArray();
      return response()->json([
          'message'=>$errores,
          'error'=>true,
      ],400);

    }

    // public function transformColaboradorToJson(User $colaborador, DetalleColaborador $colaborador_ext){
    //     return [
    //             'nombre' => $colaborador->nombre,
    //             'apellido'=>$colaborador->apellido,
    //             'email'=> $colaborador->email,
    //             'puesto'=> $colaborador_ext->puesto,
    //             'telefono'=> $colaborador_ext->telefono,
    //             'fecha_nacimiento'=>$colaborador_ext->fecha_nacimiento,
    //     ];
    // }

    public function addFoto(Request $request, $id){
        //return $request->all();
        $foto_colaborador = FotoColaborador::where('id_colaborador',$id)->first();
        $colaborador = User::where('id',$id)->first();

        try{

            if($request->file('image')->isValid()){
              if (!$foto_colaborador) {
                $foto_colaborador = new FotoColaborador;
              }
                DB::beginTransaction();
                $foto_colaborador->id_colaborador = $colaborador->id;
                $foto_colaborador->url_foto = $this->uploadFilesS3($request->image,$colaborador->id);
                $colaborador->foto()->save($foto_colaborador);
                $foto_colaborador['ext'] = $request->image->getClientOriginalExtension();
                DB::commit();
                return response()->json([
                    'error'=>false,
                    'messages'=>'Foto actualizada.',
                    'data'=>$foto_colaborador
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
      $foto_colaborador = FotoColaborador::where('id_colaborador',$id)->first();

      // if ($foto_colaborador->isEmpty()) {
      //   return response()->json([
      //     'error'=>true,
      //     'message'=>'Foto no encontrada.'
      //   ],400);
      // }

      try {
        DB::beginTransaction();
        $foto_colaborador->delete();
        DB::commit();

        return response()->json([
          'error'=>false,
          'message'=>'Foto elimiada correctamente.'
        ],200);

      } catch (Exception $e) {
        DB::rollBack();
        Bugsnag::notifyException(new RuntimeException("El usuario no pudo borrar foto de perfil"));
        return response()->json([
          'error'=>true,
          'message'=>$e
        ],400);
      }

    }

    public function getRecordatoriosColaborador($id){
        $user = User::find($id);
        return $user->recordatorioColaborador;
    }

    public function addRecordatorio(Request $request){
        try{
            DB::beginTransaction();
            $recordatorio = new RecordatorioColaborador;
            $recordatorio->id_colaborador = $request->id_colaborador;
            $recordatorio->nota = $request->nota_recordatorio;
            $recordatorio->hora = $request->hora_recordatorio;
            $recordatorio->fecha = $request->fecha_recordatorio;
            $recordatorio->save();

            DB::commit();

            return response()->json([
                'error'=>false,
                'message'=>'success',
                'data'=>$recordatorio,
            ],200);

        }catch(Exception $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo crear un recordatorio en oportunidad"));
            return response()->json([
                'error'=>true,
                'message'=>$e
            ],400);
        }
    }

    public function changeRol($status){
        $auth = $this->guard()->user();
        $auth->rol = $status;
        $auth->save();

        return response()->json([
            'error'=>false,
            'message'=>'success',
            'data'=>"Status: {$status}"
        ],200);
    }
    
    public function uploadFilesS3($file, $colaborador){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('colaborador/foto_perfil/'.$colaborador,'s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function StatusChecker($catalogo,$consulta){

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

                            if( $catalogo[$i]->status == $consulta[$j]->status ){
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

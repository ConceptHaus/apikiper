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
use Illuminate\Support\Facades\Storage;



use DB;
use Mail;
use Mailgun;

class ColaboradoresController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'apellido'=> 'required|string|max:255',
            'is_admin'=> 'required|boolean|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            //'fecha_nacimiento'=> 'required|string|max:255'
        ]);
    }
    protected function validatorUpdate(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            // 'is_admin'=> 'required|boolean|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            //'fecha_nacimiento'=> 'required|string|max:255'
        ]);
    }

    protected function validatorDelete(array $data)
    {
        return Validator::make($data, [
            'id_borrar' => 'required|exists:users,id',
            'id_asignar'=> 'required|exists:users,id'
        ]);
    }

    public function registerColaborador(Request $request){
            $validator = $this->validator($request->all());

            if($validator->passes()){
                 try{

                    DB::beginTransaction();
                    $colaborador = new User;
                    $colaborador->nombre = $request->nombre;
                    $colaborador->apellido = $request->apellido;
                    $colaborador->email = $request->email;
                    $pass = str_random(8);
                    $colaborador->password = bcrypt($pass);
                    $colaborador->is_admin = $request->is_admin;
                    $colaborador->status = 1;

                    $colaborador_ext = new DetalleColaborador;
                    $colaborador_ext->puesto = $request->puesto;
                    $colaborador_ext->telefono = $request->telefono;
                    $colaborador_ext->celular = intval(preg_replace('/[^0-9]+/', '', $request->celular),10);
                    $colaborador_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
                    $colaborador_ext->fecha_nacimiento = $request->fecha_nacimiento;
                    $colaborador->save();
                    $colaborador->detalle()->save($colaborador_ext);

                    $foto_colaborador = new FotoColaborador;
                    $foto_colaborador->url_foto = 'https://s3.us-east-2.amazonaws.com/kiperbucket/generales/kiper-default.svg';

                    $colaborador->foto()->save($foto_colaborador);

                    $arrayColaborador = $colaborador->toArray();
                    $arrayColaborador['pass'] = $pass;

                    Mailgun::send('auth.emails.register',$arrayColaborador,function ($contacto) use ($arrayColaborador){
                       // $message->tag('myTag');
                       $contacto->from('contacto@kiper.app', 'Kiper');
                       // $message->testmode(true);
                       $contacto->subject('Termina tu registro en Kiper');
                       $contacto->to($arrayColaborador['email'],$arrayColaborador['nombre']);
                   });

                    DB::commit();
                    return response()->json([
                        'message'=>'Registro Correcto',
                        'error'=>false,
                        'data'=> $this->transformColaboradorToJson($colaborador,$colaborador_ext)
                    ],200);

                }catch(Excpetion $e){
                    DB::rollBack();
                    return response()->json([
                        'message'=>$e,
                        'error'=>true
                    ],400);
                }
            }
            $errores = $validator->errors()->toArray();

            return response()->json([
                'error'=>true,
                'messages'=> $errores
            ],400);


    }

    public function getAllColaboradores(){
        $colaboradores = User::GetallUsers();

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
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_1 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',1)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_2 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',2)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();

      $status_3 = DB::table('oportunidades')
                          ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                          ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                          ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                          ->where('colaborador_oportunidad.id_colaborador',$id_user)
                          ->where('cat_status_oportunidad.id_cat_status_oportunidad',3)
                          ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                          ->get();


      $recordatorios = DB::table('recordatorios_prospecto')
                      ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                      ->where('recordatorios_prospecto.id_colaborador',$id_user)
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
          'oportunidades'=>[
              'status_1'=>$this->statusEmpty($status_1,1),
              'status_2'=>$this->statusEmpty($status_2,2),
              'status_3'=>$this->statusEmpty($status_3,3)
          ],
          'recordatorios'=>$recordatorios
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

    public function updateColaborador(Request $request){

        $validator = $this->validatorUpdate($request->all());

        if($validator->passes()){

          $id_colaborador = $request->id;
          $colaborador = User::where('id',$id_colaborador)->first();
          $colaborador_ext = DetalleColaborador::where('id_colaborador',$id_colaborador)->first();

            try{
            DB::beginTransaction();
            $colaborador->nombre = $request->nombre;
            $colaborador->apellido = $request->apellido;
            $colaborador_ext->puesto = $request->puesto;
            $colaborador_ext->telefono = $request->telefono;
            $colaborador_ext->celular = $request->celular;
            $colaborador_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
            $colaborador_ext->fecha_nacimiento = $request->fecha_nacimiento;
            $colaborador->save();
            $colaborador->detalle()->save($colaborador_ext);
            $colaboradorRes = User::GetOneUser($id_colaborador);
            DB::commit();
            return response()->json([
                'message'=>'Correcto',
                'error'=>false,
                'data'=>$colaboradorRes
                ]);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'message'=>$e,
                    'error'=>true
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
          $borrar->delete();

          DB::commit();

          return response()->json([
              'message'=>'Borrado Correctamente',
              'error'=>false,
              'data'=>$id_borrar
          ],200);


        }catch (Exception $e){

          DB::rollBack();

          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);

        }
      }


      // if ($validator->passes()) {
      //   try{
      //
      //       DB::beginTransaction();
      //       User::where('id', $id_borrar)->delete();
      //       DB::commit();
      //
      //       return response()->json([
      //           'message'=>'Borrado Correctamente',
      //           'error'=>false,
      //           'data'=>$id_borrar
      //       ],200);
      //
      //   }catch (Exception $e){
      //
      //     DB::rollBack();
      //
      //     return response()->json([
      //       'error'=>true,
      //       'message'=>$e
      //     ],400);
      //
      //   }
      // }

      $errores = $validator->errors()->toArray();
      return response()->json([
          'message'=>$errores,
          'error'=>true,
      ],400);

    }

    public function transformColaboradorToJson(User $colaborador, DetalleColaborador $colaborador_ext){
        return [
                'nombre' => $colaborador->nombre,
                'apellido'=>$colaborador->apellido,
                'email'=> $colaborador->email,
                'puesto'=> $colaborador_ext->puesto,
                'telefono'=> $colaborador_ext->telefono,
                'fecha_nacimiento'=>$colaborador_ext->fecha_nacimiento,
        ];
    }

    public function addFoto(Request $request, $id){
      // return $request->all();
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

      if ($foto_colaborador->isEmpty()) {
        return response()->json([
          'error'=>true,
          'message'=>'Foto no encontrada.'
        ],400);
      }

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

        return response()->json([
          'error'=>true,
          'message'=>$e
        ],400);
      }

    }

    public function uploadFilesS3($file, $colaborador){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('colaborador/foto_perfil/'.$colaborador,'s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }
}

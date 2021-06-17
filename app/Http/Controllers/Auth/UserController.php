<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;
use App\Modelos\Colaborador\FotoColaborador;
use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Events\Historial;
use App\Events\Event;
use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;


use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
class UserController extends Controller
{
    protected function validatorUpdate(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
            'correo' => 'required|email|max:255'
        ]);
    }

    protected function validatorUpdateMe(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255'
        ]);
    }

    protected function validatorPassword(array $data) {

      return Validator::make($data, [
        'id_colaborador'=>'required|exists:users,id',
        'password'=>'required|string|min:6'
      ]);
    }

    protected function validatorUser(array $data){
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=>'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'puesto'=> 'required|string|max:255',
            'telefono'=>'required|string|max:255'

        ]);
    }


    public function getAuthUser(Request $request){
        $id_user = $this->guard()->user()->id;
        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->select(DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $status_1 = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->where('cat_status_oportunidad.id_cat_status_oportunidad',1)
                            ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $status_2 = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id_user)
                            ->where('cat_status_oportunidad.id_cat_status_oportunidad',2)
                            ->select('cat_status_oportunidad.id_cat_status_oportunidad','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                            ->get();

        $status_3 = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->whereNull('oportunidades.deleted_at')
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
                        ->orderBy('detalle_recordatorio_prospecto.fecha_recordatorio', 'desc')
                        ->get();
        $detalle = DetalleColaborador::where('id_colaborador',$this->guard()->user()->id)
                        ->first();
        $img = FotoColaborador::where('id_colaborador', $this->guard()->user()->id)
                        ->select('url_foto')
                        ->first();
        $permisos = json_decode(Auth::user()->role->acciones);
        
        return response()->json([
            'error'=>false,
            'user'=>$this->guard()->user(),
            'detalle'=>$detalle,
            'img_perfil'=>$img,
            'status'=>$this->StatusChecker($catalogo_status,$status_genericos),
            'status_genericos'=>$status_genericos,
            'oportunidades'=>[
                'status_1'=>$this->statusEmpty($status_1,1),
                'status_2'=>$this->statusEmpty($status_2,2),
                'status_3'=>$this->statusEmpty($status_3,3),
            ],
            'recordatorios'=>$recordatorios,
            'activity'=>$this->guard()->user()->actions,
            'permisos'=>$permisos
        ],200);

    }

    public function updateMe(Request $request){
        $auth = $this->guard()->user();
        $id_me = $this->guard()->user()->id;
        $me = User::where('id',$id_me)->first();
        $me_ext = DetalleColaborador::where('id_colaborador',$id_me)->first();
        $validator = $this->validatorUpdateMe($request->all());
        
        
        if($validator->passes()){
            try{
                
                DB::beginTransaction();
                $me->nombre = $request->nombre;
                $me->apellido = $request->apellido;
                $me_ext->puesto = $request->puesto;
                $me_ext->telefono = $request->telefono;
                $me_ext->celular = $request->celular;
                $me_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
                $me->save();
                $me->detalle()->save($me_ext);
                $meRes = User::GetOneUser($id_me);
                DB::commit();
                
                //Historial
                $actividad = activity()
                        ->performedOn($me)
                        ->causedBy($auth)
                        ->withProperties(['accion'=>'Editó','color'=>'#ffcf4c'])
                        ->useLog('perfil_colaborador')
                        ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion su perfil. </span>');
                
                event( new Historial($actividad));

                return response()->json([
                    'message'=>'Success',
                    'error'=>false,
                    'data'=>$meRes,
                    ]);

            }catch(Exception $e){
                
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("El usuario no pudo editar su perfil"));
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

    public function onBoarding(Request $request){
        $auth = $this->guard()->user();
        if(isset($auth)){
            if($auth->onbarding_tour == 0){
                $auth->onbarding_tour = 1;
                $auth->save();
                return response()->json(['tour'=>true]);
            }
            return response()->json(['tour'=>false]);    
        }

        return response()->json([
            'error'=>false,
            'message'=>'No user get.'
        ],204);
        
    }

    public function createUser(Request $request){
        $validator = $this->validatorUser($request->all());
        if($validator->passes()){
            try{
                DB::beginTransaction();
                $user = new User;
                $user->nombre = $request->nombre;
                $user->apellido = $request->apellido;
                $user->email = $request->email;
                $pass = str_random(8);
                $user->password = bcrypt($pass);
                $user->is_admin = 1;
                $user->save();

                $user_ext = new DetalleColaborador;
                $user_ext->puesto = $request->puesto;
                $user_ext->telefono = $request->telefono;
                $user_ext->celular = intval(preg_replace('/[^0-9]+/', '', $request->celular),10);
                $user_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $request->celular), 10);
                $user_ext->fecha_nacimiento = Carbon::now()->toDateTimeString();

                $user->detalle()->save($user_ext);

                $foto_user = new FotoColaborador;
                $foto_user->url_foto = 'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/kiper-default.svg';

                $user->foto()->save($foto_user);

                $array = $user->toArray();
                $array['pass'] = $pass;
                $array['link'] = env('URL_FRONT');
                $array['dominio'] = env('DOMINIO');
                Mailgun::send('auth.emails.register',$array,function ($contacto) use ($array){
                       $contacto->tag('new_user_kiper');
                       $contacto->from('contacto@kiper.app', 'Kiper');
                       $contacto->subject('Termina tu registro en Kiper');
                       $contacto->to($array['email'],$array['nombre']);
                   });
                DB::commit();
                return response()->json([
                    'message'=>'Registro exitoso',
                    'error'=>false,
                    'data'=>['user'=>$user->email,'pass'=>$pass,'dominio'=>$array['dominio']]
                ]) ;
                

            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("No se pudo agregar un colaborador"));
                return response()->json([
                    'message'=>$e,
                    'error'=>true
                ],500);
            }
        }else{

            return response()->json([
                    'message'=>$e,
                    'error'=>true
            ],500);
        }
    }


       /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL()
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    public function changePassword(Request $request){

      $validator = $this->validatorPassword($request->all());

      if ($validator->passes()) {
        try {
          DB::beginTransaction();
          $colaborador = User::where('id', $request->id_colaborador)->first();
          $colaborador->password = bcrypt($request->password);
          $colaborador->save();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Contraseña actualizada correctamente.'
          ],200);

        } catch (Exception $e) {
          DB::rollBack();
          Bugsnag::notifyException(new RuntimeException("El usuario no pudo cambiar su contraseña"));
          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }
      }

      $errores = $validator->errors()->toArray();
      return response()->json([
        'error'=>true,
        'message'=>$errores
      ],400);
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

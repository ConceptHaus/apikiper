<?php

namespace App\Http\Repositories\Colaboradores;

use App\Modelos\User;
use App\Modelos\Colaborador\DetalleColaborador;
use App\Modelos\Colaborador\FotoColaborador;
use App\Events\Historial;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;

use Mailgun;
use DB;
use RuntimeException;


class ColaboradoresRep
{
    
    public static function getRolesByRoleID($role_id){
        return Role::all()->where('id', '<=', $role_id)->where('is_visible', '=', 1);
    }

    public static function getColaborador($id_colaborador)
    {
        return User::where('id',$id_colaborador)->first();
    }

    public static function getColaboradorExt($id_colaborador)
    {
        return DetalleColaborador::where('id_colaborador',$id_colaborador)->first();
    }

    public static function updateColaborador($colaborador_info, $colaborador,  $colaborador_ext, $auth)
    {
        try{
            DB::beginTransaction();
            $colaborador->nombre = $colaborador_info['nombre'];
            $colaborador->apellido = $colaborador_info['apellido'];
            $colaborador->role_id = $colaborador_info['role_id'];
            $colaborador_ext->puesto = $colaborador_info['puesto'];
            $colaborador_ext->telefono = $colaborador_info['telefono'];
            $colaborador_ext->celular = $colaborador_info['celular'];
            $colaborador_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $colaborador_info['celular']), 10);
            $colaborador_ext->fecha_nacimiento = $colaborador_info['fecha_nacimiento'];
            $colaborador->save();
            $colaborador->detalle()->save($colaborador_ext);
            $colaboradorRes = User::GetOneUser($colaborador_info['id']);
            DB::commit();

            //Historial
                $actividad = activity()
                    ->performedOn($colaborador)
                    ->causedBy($auth)
                    ->withProperties(['accion'=>'Editó','color'=>'#ffcf4c'])
                    ->useLog('colaborador')
                    ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion el perfil de un colaborador.</span>');
                
                event( new Historial($actividad));

            $response = ['error' => false, 'data' => $colaboradorRes];
            return $response;

            }catch(Exception $e){
                DB::rollBack();
                Bugsnag::notifyException(new RuntimeException("El usuario no pudo editar el perfil de un colaborador"));

                $response = ['error' => true, 'message' => $e];
                return $response;
            }
    }

    public static function createColaborador($new_colaborador, $auth)
    {
        try{
            DB::beginTransaction();
            
            $colaborador = new User;
            $colaborador->nombre = $new_colaborador['nombre'];
            $colaborador->apellido = $new_colaborador['apellido'];
            $colaborador->email = $new_colaborador['email'];
            $pass = str_random(8);
            $colaborador->password = bcrypt($pass);
            $colaborador->role_id = $new_colaborador['role_id'];
            $colaborador->status = 1;

            $colaborador_ext = new DetalleColaborador;
            $colaborador_ext->puesto = $new_colaborador['puesto'];
            $colaborador_ext->telefono = $new_colaborador['telefono'];
            $colaborador_ext->celular = intval(preg_replace('/[^0-9]+/', '', $new_colaborador['celular']),10);
            $colaborador_ext->whatsapp = '521'.intval(preg_replace('/[^0-9]+/', '', $new_colaborador['celular']), 10);
            $colaborador_ext->fecha_nacimiento = $new_colaborador['fecha_nacimiento'];
            $colaborador->save();
            $colaborador->detalle()->save($colaborador_ext);

            $foto_colaborador = new FotoColaborador;
            if(!isset($request->image))
                $foto_colaborador->url_foto = 'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/kiper-default.svg';
            else{
                $foto_colaborador->url_foto =ColaboradoresRep::uploadFilesS3($request->image, $request->image->getClientOriginalName());
            }

            $colaborador->foto()->save($foto_colaborador);

            $arrayColaborador = $colaborador->toArray();
            $arrayColaborador['pass'] = $pass;
            $arrayColaborador['link'] = env('https://system-demo.kiper.app');
            $arrayColaborador['dominio'] = env('DOMINIO');

            Mailgun::send('auth.emails.register',$arrayColaborador,function ($contacto) use ($arrayColaborador){
               // $message->tag('myTag');
               $contacto->from('contacto@kiper.app', 'Kiper');
               // $message->testmode(true);
               $contacto->subject('Termina tu registro en Kiper');
               $contacto->to($arrayColaborador['email'],$arrayColaborador['nombre']);
           });

            DB::commit();
            //Historial
                $actividad = activity('colaborador')
                        ->performedOn($colaborador)
                        ->causedBy($auth)
                        ->withProperties(['accion'=>'Agregó','color'=>'#39ce5f'])
                        ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un nuevo colaborador.</span>');
                        
                event( new Historial($actividad));

            $response   = array('message'   => 'Registro Correcto',
                                'error'     => false,
                                'data'      => ColaboradoresRep::transformColaboradorToJson($colaborador,$colaborador_ext));

        }catch(Excpetion $e){
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo agregar un colaborador"));
            
            $response   = array('message'   => $e,
                                'error'     => true);
        }

        return $response;
    }

    public static function uploadFilesS3($file, $colaborador){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('colaborador/foto_perfil/'.$colaborador,'s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }

    public static function transformColaboradorToJson(User $colaborador, DetalleColaborador $colaborador_ext){
        return [
                'nombre' => $colaborador->nombre,
                'apellido'=>$colaborador->apellido,
                'email'=> $colaborador->email,
                'puesto'=> $colaborador_ext->puesto,
                'telefono'=> $colaborador_ext->telefono,
                'fecha_nacimiento'=>$colaborador_ext->fecha_nacimiento,
        ];
    }

}

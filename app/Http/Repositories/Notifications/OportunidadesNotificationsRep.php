<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Http\Services\UtilService;

class OportunidadesNotificationsRep
{
    public static function getOportunidadesToSendNotifications($start_date)
    {
        $end_date = date('Y-m-d H:i:s');

        $oportunidades =    Oportunidad::select('oportunidades.id_oportunidad',
                                                'oportunidades.nombre_oportunidad',
                                                'status_oportunidad.updated_at',
                                                'detalle_oportunidad.valor',
                                                'cat_status_oportunidad.status',
                                                'users.id as colaborador_id',
                                                'users.nombre',
                                                'users.apellido',
                                                'users.email',
                                                'users.id as colaborador_id')
                                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                        ->where('status_oportunidad.updated_at', '<=', $start_date)
                                        ->groupBy('oportunidades.id_oportunidad')
                                        ->get()
                                        ->toArray();
        
        return $oportunidades;
    }

    public static function getOportunidadesToEscalateForAdmin($max_notification_attempts)
    {
        
        $oportunidades  = Notification::select ('notifications.id',
                                                'notifications.colaborador_id',
                                                'notifications.source_id',
                                                'notifications.notification_type',
                                                'notifications.status as notification_status',
                                                'notifications.attempts',
                                                'detalle_oportunidad.valor',
                                                'cat_status_oportunidad.status',
                                                'notifications.inactivity_period',
                                                'users.nombre',
                                                'users.apellido',
                                                'users.email',
                                                'oportunidades.nombre_oportunidad',
                                                'detalle_oportunidad.descripcion')
                                        ->join('users','notifications.colaborador_id','users.id')
                                        ->join('status_oportunidad','notifications.source_id','status_oportunidad.id_oportunidad')
                                        ->join('detalle_oportunidad','notifications.source_id','detalle_oportunidad.id_oportunidad')
                                        ->join('oportunidades','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                        ->where('notifications.attempts', '>=', $max_notification_attempts)
                                        ->where('notifications.status', '!=', 'resuelto')
                                        ->get()
                                        ->toArray();
        
        return $oportunidades;
    }

    public static function increaseAttemptsforExisitingOportunidadNotification($oportunidad_id)
    {
        $oportunidad =  Notification::where('source_id', $oportunidad_id)
                                    ->where('notification_type', 'oportunidad')
                                    ->where(function($q) {
                                        $q->where('status', '!=', 'resuelto')
                                          ->orWhereNull('status');
                                    })
                                    ->first();

        if (!empty($oportunidad)) {
            $oportunidad->attempts = $oportunidad->attempts + 1;
            $oportunidad->save();
        }
    }

    public static function changeStatusforExisitingOportunidadNotification($oportunidad_id, $new_status)
    {
        $oportunidad = Notification::where('source_id', $oportunidad_id)->first();

        if (!empty($oportunidad)) {
            $oportunidad->status = $new_status;
            $oportunidad->save();
        }
    }

    public static function createOportunidadNotification($oportunidad)
    {
        $notificaton                    = new Notification;
        $notificaton->colaborador_id    = $oportunidad['colaborador_id'];
        $notificaton->source_id         = $oportunidad['id_oportunidad'];
        $notificaton->notification_type = 'oportunidad';
        $notificaton->inactivity_period = $oportunidad['inactivity_period'];
        $notificaton->view            = 'no-leido';
        $notificaton->attempts          = $oportunidad['attempts'];
        $notificaton->save();
    }

    public static function checkOportunidadNotification($oportunidad_id)
    {
        $exisiting_notification = Notification::where('source_id', $oportunidad_id)->first();
       
        if (!empty($exisiting_notification)) {
            return $exisiting_notification;
        }else{
            return [];
        }
    }

    public static function updateAttemptsAndInactivityforExisitingOportunidadNotification($oportunidad_id, $new_inactivity_period, $attempts=NULL)
    {
        $oportunidad =  Notification::where('source_id', $oportunidad_id)
                                    ->where('notification_type', 'oportunidad')
                                    ->where(function($q) {
                                        $q->where('status', '!=', 'resuelto')
                                          ->orWhereNull('status');
                                    })
                                    ->first();

        if (!empty($oportunidad)) {
            $oportunidad->inactivity_period = $new_inactivity_period;
            if(!is_null($attempts)){
                $oportunidad->attempts = $oportunidad->attempts + 1;
            }
            $oportunidad->save();

            return $oportunidad;
        }
    }

    public static function getExisitingOportunidadesNotifications()
    {
        return  Notification::where('notification_type', 'oportunidad')
                            ->where(function($q) {
                                $q->where('status', '!=', 'resuelto')
                                  ->orWhereNull('status');
                            })
                            ->get()
                            ->toArray();   
    }

    public static function getOportunidadesByColaboradorToSendNotifications($user_id, $start_date)
    {
        $oportunidades =    Oportunidad::select('oportunidades.id_oportunidad',
                                                'oportunidades.nombre_oportunidad',
                                                'status_oportunidad.updated_at',
                                                'detalle_oportunidad.valor',
                                                'cat_status_oportunidad.status',
                                                'users.id as colaborador_id',
                                                'users.nombre',
                                                'users.apellido',
                                                'users.email',
                                                'users.id as colaborador_id')
                                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                        ->where('status_oportunidad.updated_at', '<=', $start_date)
                                        ->where('colaborador_oportunidad.id_colaborador', $user_id)
                                        ->groupBy('oportunidades.id_oportunidad')
                                        ->get()
                                        ->toArray();
        
        return $oportunidades;
    }

    public static function verifyActivityforOportunidad($source_id, $notificaton_updated_at)
    {
        $inactivity_period = 0;

        $status_oportunidad = StatusOportunidad::select('*')
                                                ->where('id_oportunidad', $source_id)
                                                ->first();
        
        if (isset($status_oportunidad->updated_at)) {
            $inactivity_period = UtilService::getHoursDifferenceForTimeStamps($status_oportunidad->updated_at, $notificaton_updated_at);
        }

        return $inactivity_period;
    }

}

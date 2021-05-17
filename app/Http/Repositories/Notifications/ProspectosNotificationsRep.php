<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Prospecto\Prospecto;

class ProspectosNotificationsRep
{
    public static function getProspectosToSendNotifications($start_date){
        
        $end_date = date('Y-m-d H:i:s');

        $prospectos = Prospecto::select('prospectos.id_prospecto',
                                        'prospectos.nombre',
                                        'status_prospecto.updated_at',
                                        'detalle_prospecto.telefono',
                                        'cat_status_prospecto.status',
                                        'users.nombre',
                                        'users.apellido',
                                        'users.email',
                                        'users.id as colabrador_id')
                                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('users','colaborador_prospecto.id_colaborador','users.id')
                                ->join('status_prospecto','colaborador_prospecto.id_prospecto','status_prospecto.id_prospecto')
                                ->join('detalle_prospecto','colaborador_prospecto.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                ->where('status_prospecto.updated_at', '<=', $start_date)
                                ->groupBy('prospectos.id_prospecto')
                                ->get();
        
        return $prospectos;
    }

    public static function getProspectosToEscalateForAdmin($max_notification_attempts){
        
        $prospectos =  Notification::select('notifications.id',
                                            'notifications.colaborador_id',
                                            'notifications.source_id',
                                            'notifications.notification_type',
                                            'notifications.status as notification_status',
                                            'notifications.attempts',
                                            'notifications.inactivity_period',
                                            'cat_status_prospecto.status',
                                            'users.nombre',
                                            'users.apellido',
                                            'users.email')
                                    ->join('users','notifications.colaborador_id','users.id')
                                    ->join('status_prospecto','notifications.source_id','status_prospecto.id_prospecto')
                                    ->join('detalle_prospecto','notifications.source_id','detalle_prospecto.id_prospecto')
                                    ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                    ->where('notifications.attempts', '>=', $max_notification_attempts)
                                    ->where('notifications.status', '!=', 'resuelto')
                                    ->get();
        
        return $prospectos;
    }

    public static function increaseAttemptsforExisitingProspectoNotification($prospecto_id)
    {
        $prospecto = Notification::where('source_id', $prospecto_id)->where('notification_type', 'prospecto')->first();

        if (!empty($prospecto)) {
            $prospecto->attempts = $prospecto->attempts + 1;
            $prospecto->save();
        }
    }

    public static function changeStatusforExisitingProspectoNotification($prospecto_id, $new_status)
    {
        $prospecto = Notification::where('source_id', $prospecto_id)->first();

        if (!empty($prospecto)) {
            $prospecto->status = $new_status;
            $prospecto->save();
        }
    }

}

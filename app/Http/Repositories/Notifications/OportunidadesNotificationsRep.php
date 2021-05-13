<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Oportunidad\Oportunidad;

class OportunidadesNotificationsRep
{
    public static function getOportunidadesToSendNotifications($start_date){
        
        $end_date = date('Y-m-d H:i:s');

        $oportunidades =    Oportunidad::select('oportunidades.id_oportunidad',
                                                'oportunidades.nombre_oportunidad',
                                                'status_oportunidad.updated_at',
                                                'detalle_oportunidad.valor',
                                                'cat_status_oportunidad.status',
                                                'users.nombre',
                                                'users.apellido',
                                                'users.email')
                                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                                        ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                                        ->join('detalle_oportunidad','colaborador_oportunidad.id_oportunidad','detalle_oportunidad.id_oportunidad')
                                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                        ->where('status_oportunidad.updated_at', '<=', $start_date)
                                        ->groupBy('oportunidades.id_oportunidad')
                                        ->get();
        
        return $oportunidades;
    }

    public static function getOportunidadesToEscalateForAdmin($attempts){
        
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
                                                'users.email')
                                        ->join('users','notifications.colaborador_id','users.id')
                                        ->join('status_oportunidad','notifications.source_id','status_oportunidad.id_oportunidad')
                                        ->join('detalle_oportunidad','notifications.source_id','detalle_oportunidad.id_oportunidad')
                                        ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                                        ->where('notifications.attempts', '>=', $attempts)
                                        ->where('notifications.status', '!=', 'resuelto')
                                        ->get();
        
        return $oportunidades;
    }

}

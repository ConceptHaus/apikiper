<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Prospecto\Prospecto;

class ProspectosNotificationsRep
{
    public static function getProspectosToSendNotifications($start_date){
        
        $end_date = date('Y-m-d H:i:s');

        $prospectos =    Prospecto::select('prospectos.id_prospecto',
                                                'prospectos.nombre',
                                                'status_prospecto.updated_at',
                                                'detalle_prospecto.telefono',
                                                'cat_status_prospecto.status',
                                                'users.nombre',
                                                'users.apellido',
                                                'users.email')
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

}

<?php

namespace App\Http\Services\Notifications;

use App\Http\Services\OneSignal\OneSignalService;

class SendNotificationService
{

    public static function sendInactiveOportunityNotification($oportunity)
    {
        OneSignalService::sendNotification(
            $oportunity['colaborador_id'],
            'Oportunidad ' . $oportunity['nombre_oportunidad'],
            'Oportunidad ' . $oportunity['nombre_oportunidad'] . ' sin actividad',
            'inactive_oportunity',
            $oportunity['id_oportunidad']
        );
    }

    public static function sendInactiveProspectNotification($prospect)
    {
        OneSignalService::sendNotification(
            $prospect['colaborador_id'],
            'Prospecto ' . $prospect['nombre_prospecto'],
            'Prospecto '.$prospect['nombre_prospecto'].' sin actividad',
            'inactive_prospect',
            $prospect['id_prospecto']
        );
    }
}

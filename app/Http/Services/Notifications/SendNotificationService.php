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
            'INACTIVE_OPORTUNITY',
            $oportunity
        );
    }

    public static function sendInactiveProspectNotification($prospect)
    {
        OneSignalService::sendNotification(
            $prospect['colaborador_id'],
            'Prospecto ' . $prospect['nombre_oportunidad'],
            'Prospecto '.$prospect['nombre_prospecto'].' sin actividad',
            'INACTIVE_PROSPECT',
            $prospect
        );
    }
}

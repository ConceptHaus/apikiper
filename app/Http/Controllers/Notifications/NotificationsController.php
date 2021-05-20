<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Notifications\OportunidadesNotificationsService;
use App\Http\Services\Notifications\ProspectosNotificationsService;
use Auth;
use Mail;

class NotificationsController extends Controller
{
    /*
    | Oportunidades
    */

    public function getOportunidadesToSendNotifications()
    {
        return OportunidadesNotificationsService::getOportunidadesToSendNotifications();
    }

    public function getOportunidadesToEscalateForAdmin()
    {
       return OportunidadesNotificationsService::getOportunidadesToEscalateForAdmin();
    }

    /*
    | Prospectos
    */

    public function getProspectosToSendNotifications(){
        return ProspectosNotificationsService::getProspectosToSendNotifications();
    }

    public function getProspectosToEscalateForAdmin()
    {
       return ProspectosNotificationsService::getProspectosToEscalateForAdmin();
    }

    /*
    | Cron
    */

    public function sendNotifications()
    {
        OportunidadesNotificationsService::sendNotifications();
        ProspectosNotificationsService::sendNotifications();
    }
}

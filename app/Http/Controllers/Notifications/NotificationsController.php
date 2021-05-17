<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Notifications\OportunidadesNotificationsService;
use App\Http\Services\Notifications\ProspectosNotificationsService;
use Auth;


class NotificationsController extends Controller
{
    public function getOportunidadesToSendNotifications(){
        return OportunidadesNotificationsService::getOportunidadesToSendNotifications();
    }

    public function getProspectosToSendNotifications(){
        return ProspectosNotificationsService::getProspectosToSendNotifications();
    }

    public function insertProspectosToSendNotifications(){
        $prospectos = ProspectosNotificationsService::getProspectosToSendNotifications();
        return ProspectosNotificationsService::insertProspectosToSendNotifications($prospectos);
    }

    public function countNotifications(){
        return 5;
    }
}

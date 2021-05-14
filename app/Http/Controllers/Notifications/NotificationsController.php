<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Notifications\OportunidadesNotificationsService;
use Auth;


class NotificationsController extends Controller
{
    public function getOportunidadesToSendNotifications()
    {
        return OportunidadesNotificationsService::getOportunidadesToSendNotifications();
    }

    public function getOportunidadesToEscalateForAdmin()
    {
       return OportunidadesNotificationsService::getOportunidadesToEscalateForAdmin();
    }

}

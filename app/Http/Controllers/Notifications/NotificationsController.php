<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Notifications\OportunidadesNotificationsService;
use App\Http\Services\Notifications\ProspectosNotificationsService;
use Auth;
use Mail;
use App\Http\Services\Auth\AuthService;


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

    public function escalateNotifications()
    {
        OportunidadesNotificationsService::escalateNotifications();
        ProspectosNotificationsService::escalateNotifications();
    }
    
    public function countNotifications(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 

        return ProspectosNotificationsService::getCountNotifications($auth->id);
    }

    public function getProspectosNotifications(Request $request){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo();
        
        $limit = $request->limit;

        if ($limit == 0) {
            $limit = 3;
        } else {
            $limit = $limit + 2;
        }
        
        return ProspectosNotificationsService::getProspectosNotifications($auth->id, $limit);
    }

    public function getOportunidadesNotifications(Request $request){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo();
        $limit = $request->limit;

        if ($limit == 0) {
            $limit = 3;
        } else {
            $limit = $limit + 2;
        }
        
        return ProspectosNotificationsService::getOportunidadesNotifications($auth->id, $limit);
    }

    public function updateStatusNotification(Request $request){
        $source_id = $request->source_id;

        return ProspectosNotificationsService::updateStatusNotification($source_id);
    }

    public function postSettingNotificationAdmin(Request $request){
        return ProspectosNotificationsService::postSettingNotificationAdmin($request);
    }

    public function postSettingNotificationColaborador(Request $request){
        return ProspectosNotificationsService::postSettingNotificationColaborador($request);
    }

    public function getSettingNotificationColaborador(Request $request){
        return ProspectosNotificationsService::getSettingNotificationColaborador($request);
    }

    public function getSettingNotificationAdministrador(Request $request){
        return ProspectosNotificationsService::getSettingNotificationAdministrador($request);
    }
}

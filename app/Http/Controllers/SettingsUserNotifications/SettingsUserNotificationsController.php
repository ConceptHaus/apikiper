<?php

namespace App\Http\Controllers\SettingsUserNotifications;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\SettingsUserNotifications\SettingsUserNotificationsService;
use Auth;
use Mail;
use App\Http\Services\Auth\AuthService;


class SettingsUserNotificationsController extends Controller
{
    public function postSettingNotificationAdmin(Request $request){
        return SettingsUserNotificationsService::postSettingNotificationAdmin($request);
    }

    public function postSettingNotificationColaborador(Request $request){
        return SettingsUserNotificationsService::postSettingNotificationColaborador($request);
    }

    public function getSettingNotificationColaborador(Request $request){
        return SettingsUserNotificationsService::getSettingNotificationColaborador($request);
    }

    public function getSettingNotificationAdministrador(){
        return SettingsUserNotificationsService::getSettingNotificationAdministrador();
    }
}

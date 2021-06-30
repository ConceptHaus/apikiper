<?php

namespace App\Http\Repositories\SettingsUserNotifications;

use App\Modelos\Setting;
use App\Modelos\SettingUserNotification;
use DB;

class SettingsUserNotificationsRep
{

    public static function postSettingNotificationAdmin($params){
        $pros_max_inac = $params["max_time_prospect"]."|".$params["timeP"];
        $opo_max_inac = $params["max_time_oportu"]."|".$params["opor_reciv_inact"];

        $prospectos_max_time_inactivity = Setting::where('setting', 'prospectos_max_time_inactivity')->first();
        $prospectos_max_time_inactivity->value = $pros_max_inac;
        $prospectos_max_time_inactivity->save();

        $prospectos_max_notification_attempt = Setting::where('setting', 'prospectos_max_notification_attempt')->first();
        $prospectos_max_notification_attempt->value = $params["max_time_attempt_prospect"];
        $prospectos_max_notification_attempt->save();

        $prospectos_receive_inactivity_notifications = Setting::where('setting', 'prospectos_receive_inactivity_notifications')->first();
        $prospectos_receive_inactivity_notifications->value = $params["prosp_reciv_inact"];
        $prospectos_receive_inactivity_notifications->save();

        $oportunidades_max_time_inactivity = Setting::where('setting', 'oportunidades_max_time_inactivity')->first();
        $oportunidades_max_time_inactivity->value = $opo_max_inac;
        $oportunidades_max_time_inactivity->save();

        $oportunidades_max_notification_attempt = Setting::where('setting', 'oportunidades_max_notification_attempt')->first();
        $oportunidades_max_notification_attempt->value = $params["max_time_attempt_oport"];
        $oportunidades_max_notification_attempt->save();

        $oportunidades_receive_inactivity_notifications = Setting::where('setting', 'oportunidades_receive_inactivity_notifications')->first();
        $oportunidades_receive_inactivity_notifications->value = $params["oport_reciv_inact"];
        $oportunidades_receive_inactivity_notifications->save();

        return response()->json([
            'error'=>false,
            'mensaje'=>"Se guardo la configuración con exito"
        ]); 
    }

    

    public static function postSettingNotificationColaborador($params){
        
        if (is_null($params->disable_email_notification_prospectos)) {
            $params->disable_email_notification_prospectos = false;
        }
        if (is_null($params->disable_email_notification_oportunidades)) {
            $params->disable_email_notification_oportunidades = false;
        }
        if (is_null($params->disable_email_notification_escalated_prospectos)) {
            $params->disable_email_notification_escalated_prospectos = false;
        }
        if (is_null($params->disable_email_notification_escalated_oportunidades)) {
            $params->disable_email_notification_escalated_oportunidades = false;
        }

        $configuraciones = json_encode(array(
            'disable_email_notification_prospectos' => $params->disable_email_notification_prospectos,
            'disable_email_notification_oportunidades' => $params->disable_email_notification_oportunidades,
            'disable_email_notification_escalated_prospectos' => $params->disable_email_notification_escalated_prospectos,
            'disable_email_notification_escalated_oportunidades' => $params->disable_email_notification_escalated_oportunidades,
            'oportunidades_max_time_inactivity' => $params->max_time_oportu_colab.'|'.$params->timeOC,
            'prospectos_max_time_inactivity' => $params->max_time_prospect_colab.'|'.$params->timePC
        ));

        $usuario = SettingUserNotification::where('id_user', $params->idUsers)->first();
        
        if ($usuario) {
            $update = SettingUserNotification::where('id_user', $usuario['id_user'])->first();
            $update->configuraciones = $configuraciones;
            $update->save();
        } else {
            $settingColaborador = new SettingUserNotification;
            $settingColaborador->id_user = $params->idUsers;
            $settingColaborador->configuraciones = $configuraciones;
            $settingColaborador->save();
        }

        return response()->json([
            'error'=>false,
            'mensaje'=>"Se guardo la configuración con exito"
        ]);
        
    }

    public static function getSettingNotificationColaborador($id_user){

       $usuario = SettingUserNotification::where('id_user', $id_user)->first();
       return $usuario;
        
    }

    public static function getSettingsNotificationColaborador($id_user){

        $usuario = SettingUserNotification::where('id_user', $id_user)->get()->toArray();
        return $usuario;
         
     }

    public static function getSettingNotificationAdministrador(){

        return $settings = Setting::all();
         
     }

    public static function getSettingNotificationUser($id_user){
        return DB::table('settings_user')
        ->where('id_user', $id_user)
        ->get()
        ->toArray();
    }

    public static function getUsersWithSettings()
    {
        return  SettingUserNotification::all();  
    }

}

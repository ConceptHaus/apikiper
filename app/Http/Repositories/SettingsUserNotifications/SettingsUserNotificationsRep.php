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

        $oportunidades_status_max_count = Setting::where('id', 1)->first();
        $oportunidades_status_max_count->value = $params["max_time_attempt_oport"];
        $oportunidades_status_max_count->save();

        $prospectos_max_time_inactivity = Setting::where('id', 2)->first();
        $prospectos_max_time_inactivity->value = $pros_max_inac;
        $prospectos_max_time_inactivity->save();

        $prospectos_max_notification_attempt = Setting::where('id', 3)->first();
        $prospectos_max_notification_attempt->value = $params["max_time_attempt_prospect"];
        $prospectos_max_notification_attempt->save();

        $prospectos_receive_inactivity_notifications = Setting::where('id', 4)->first();
        $prospectos_receive_inactivity_notifications->value = $params["prosp_reciv_inact"];
        $prospectos_receive_inactivity_notifications->save();

        $oportunidades_max_time_inactivity = Setting::where('id', 5)->first();
        $oportunidades_max_time_inactivity->value = $opo_max_inac;
        $oportunidades_max_time_inactivity->save();

        $oportunidades_max_notification_attempt = Setting::where('id', 6)->first();
        $oportunidades_max_notification_attempt->value = $params["max_time_attempt_oport"];
        $oportunidades_max_notification_attempt->save();

        $oportunidades_receive_inactivity_notifications = Setting::where('id', 7)->first();
        $oportunidades_receive_inactivity_notifications->value = $params["oport_reciv_inact"];
        $oportunidades_receive_inactivity_notifications->save();
    }

    

    public static function postSettingNotificationColaborador($params){
        
        if (is_null($params->disable_email_notification_prospectos)) {
            $params->disable_email_notification_prospectos = false;
        }
        if (is_null($params->disable_email_notification_oportunidades)) {
            $params->disable_email_notification_oportunidades = false;
        }
        if (is_null($params->disable_email_notification_prospectos)) {
            $params->disable_email_notification_prospectos = false;
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
        
    }

    public static function getSettingNotificationColaborador($params){

       $usuario = SettingUserNotification::where('id_user', $params->id_usuario)->first();
       return $usuario;
        
    }

    public static function getSettingNotificationAdministrador($params){

        return $settings = Setting::all();
         
     }

}

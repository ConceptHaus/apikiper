<?php
namespace App\Http\Services\SettingsUserNotifications;
use App\Http\Repositories\SettingsUserNotifications\SettingsUserNotificationsRep;
use Mailgun;

use App\Http\DTOs\Settings\SettingsDTO;

class SettingsUserNotificationsService
{
    public static function postSettingNotificationAdmin($params){
        return SettingsUserNotificationsRep::postSettingNotificationAdmin($params);
    }

    public static function postSettingNotificationColaborador($params){
        return SettingsUserNotificationsRep::postSettingNotificationColaborador($params);
    }

    public static function getSettingNotificationColaborador($params){
        $settingNotification = SettingsUserNotificationsRep::getSettingNotificationColaborador($params);
        $settingNotification->configuraciones = json_decode($settingNotification->configuraciones);

        $value_prospectos = json_encode($settingNotification->configuraciones->prospectos_max_time_inactivity);
        $value_prospectos = str_replace('"', '', explode("|", $value_prospectos)); 

        $value_oportunidades = json_encode($settingNotification->configuraciones->oportunidades_max_time_inactivity);
        $value_oportunidades = str_replace('"', '', explode("|", $value_oportunidades)); 

        $settingNotification->max_time_prospect_colab = $value_prospectos[0];
        $settingNotification->timePC = $value_prospectos[1];

        $settingNotification->max_time_oportu_colab = $value_oportunidades[0];
        $settingNotification->timeOC = $value_oportunidades[1];

        return $settingNotification;
    }

    public static function getSettingNotificationAdministrador($params){
        $settings = SettingsUserNotificationsRep::getSettingNotificationAdministrador($params);
        $setting = new SettingsDTO();

        foreach ($settings as $key => $value) {

            if ($value->setting == "oportunidades_status_max_count") {
                $setting->oportunidades_status_max_count = $value->value;
            }

            if ($value->setting == "prospectos_max_time_inactivity") {
                $setting->prospectos_max_time_inactivity = $value->value;

                $max_prosp = json_encode($setting->prospectos_max_time_inactivity);
                $max_prosp = str_replace('"', '', explode("|", $max_prosp)); 
                $setting->max_prosp = $max_prosp[0];
                $setting->max_prosp_time = $max_prosp[1];
            }

            if ($value->setting == "prospectos_max_notification_attempt") {
                $setting->prospectos_max_notification_attempt = $value->value;
            }

            if ($value->setting == "prospectos_receive_inactivity_notifications") {
                $setting->prospectos_receive_inactivity_notifications = $value->value;
            }

            if ($value->setting == "oportunidades_max_time_inactivity") {
                $setting->oportunidades_max_time_inactivity = $value->value;

                $max_oportu = json_encode($setting->oportunidades_max_time_inactivity);
                $max_oportu = str_replace('"', '', explode("|", $max_oportu)); 
                $setting->max_oportu = $max_oportu[0];
                $setting->max_oportu_time = $max_oportu[1];
            }

            if ($value->setting == "oportunidades_max_notification_attempt") {
                $setting->oportunidades_max_notification_attempt = $value->value;
            }

            if ($value->setting == "oportunidades_receive_inactivity_notifications") {
                $setting->oportunidades_receive_inactivity_notifications = $value->value;
            }
            
        }

        return json_encode($setting);    
    }
}

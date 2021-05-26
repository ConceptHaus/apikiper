<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\ProspectosNotificationsRep;
use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
use Mailgun;

use App\Http\DTOs\Settings\SettingsDTO;

class ProspectosNotificationsService
{
    public static function getProspectosToSendNotifications()
    {
        $max_time_inactivity = SettingsService::getProspectosMaxTimeInactivity();
        $start_date          = UtilService::getStartDateForNotifications($max_time_inactivity);

        return ProspectosNotificationsRep::getProspectosToSendNotifications($start_date);
    }

    public static function getProspectosToEscalateForAdmin()
    {
        $max_notification_attempts = SettingsService::getProspectosMaxNotificationAttempts();
        return ProspectosNotificationsRep::getProspectosToEscalateForAdmin($max_notification_attempts);
    }

    public static function increaseAttemptsforExisitingProspectoNotification($prospecto_id)
    {
        return ProspectosNotificationsRep::increaseAttemptsforExisitingProspectoNotification($prospecto_id);
    }

    public static function changeStatusforExisitingProspectoNotification($prospecto_id, $new_status)
    {
        $statuses = UtilService::getColumnStatuses('notifications', 'status'); 
        if(UtilService::verifyNewStatusInStatuses($new_status, $statuses))
        {
            ProspectosNotificationsRep::changeStatusforExisitingProspectoNotification($prospecto_id, $new_status);
        }
    }

    public static function sendNotifications()
    {
        $notifications  = ProspectosNotificationsRep::getExisitingProspectosNotifications();
        $prospectos     = ProspectosNotificationsService::getProspectosToSendNotifications();
        
        if (count($prospectos) > 0) {
            //Delete no longer inactive prospectos from array
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    foreach ($prospectos as $index => $prospecto) {
                        if (!in_array($notification['source_id'], $prospecto)) {
                           unset($prospectos[$index]);
                           ProspectosNotificationsService::changeStatusforExisitingProspectoNotification($notification['source_id'], 'resuelto');
                        }
                    }
                }
            }
            $max_time_inactivity =  SettingsService::getProspectosMaxTimeInactivity();
            
            foreach ($prospectos as $key => $prospecto) {
                $existing_notification = ProspectosNotificationsRep::checkProspectoNotification($prospecto['id_prospecto']);
                if(isset($existing_notification->id)){
                    ProspectosNotificationsRep::updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto['id_prospecto']);
                    $prospecto['attempts']            = $existing_notification->attempts;
                    $prospecto['inactivity_period']   = $existing_notification->inactivity_period;
                    
                }else{
                    $prospecto['attempts']            = 1;
                    $prospecto['inactivity_period']   = $max_time_inactivity; 
                    ProspectosNotificationsRep::createProspectoNotification($prospecto);
                }
                // print_r($prospecto);
                ProspectosNotificationsService::sendProspectoNotificationEmail($prospecto);
            }
        }else{
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    ProspectosNotificationsService::changeStatusforExisitingProspectoNotification($notification['source_id'], 'resuelto');    
                }
            }    
        }
    }

    public static function sendProspectoNotificationEmail($prospecto)
    {
        $msg = array(
                    'subject'            => 'Prospecto '.$prospecto['nombre_prospecto'].' sin actividad',
                    'email'              => $prospecto['email'],
                    'colaborador'        => $prospecto['nombre'].' '.$prospecto['apellido'],
                    'nombre_prospecto'   => $prospecto['nombre_prospecto'],
                    'attempt'            => $prospecto['attempts'],
                    'inactivity_period'  => $prospecto['inactivity_period'],
                    'id_prospecto'       => $prospecto['id_prospecto']
                );

        Mailgun::send('mailing.inactivity_prospecto', ['msg' => $msg], function ($m) use ($msg){
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }

    public static function escalateNotifications()
    {
        $notifications =ProspectosNotificationsService::getProspectosToEscalateForAdmin();
        // print_r($notifications);
        
        if (count($notifications) > 0) {
            $admins =ProspectosNotificationsService::getAdminsToSendoportunidadNotificationEscalation(3);
            // print_r($admins);
            if (count($admins) > 0) {
                foreach ($notifications as $key => $notification) {
                   ProspectosNotificationsRep::changeStatusforExisitingProspectoNotification($notification['source_id'], 'escalado');
                   ProspectosNotificationsService::sendProspectoEscalationEmail($notification, $admins);
                }
            }
        }
    }

    public static function sendProspectoEscalationEmail($notification, $admins)
    {
        foreach ($admins as $key => $admin) {
            $msg = array(
                'subject'            => 'Escalamiento de Prospecto '.$notification['nombre_prospecto'].' por inactividad',
                'email'              => $admin['email'],
                'colaborador'        => $admin['nombre'].' '.$admin['apellido'],
                'nombre_oportunidad' => $notification['nombre_prospecto'],
                'attempt'            => $notification['attempts'],
                'inactivity_period'  => $notification['inactivity_period'],
                'id_prospecto'       => $notification['source_id'],
                'admin'              => $admin['nombre'].' '.$admin['apellido'],
            );

            Mailgun::send('mailing.inactivity_escaleted_prospecto', ['msg' => $msg], function ($m) use ($msg){
                $m->to($msg['email'], $msg['admin'])->subject($msg['subject']);
                $m->from('notificaciones@kiper.com.mx', 'Kiper');
            });
        }  
    }
    
    public static function getAdminsToSendoportunidadNotificationEscalation($role_id)
    {
        return UsersRep::getUsersByRoleId($role_id);    
    }

    public static function getCountNotifications($id_user){
        return ProspectosNotificationsRep::getCountNotifications($id_user);
    }

    public static function getProspectosNotifications($id_user, $limit){
        return ProspectosNotificationsRep::getProspectosNotifications($id_user, $limit);
    }

    public static function getOportunidadesNotifications($id_user, $limit){
        return ProspectosNotificationsRep::getOportunidadesNotifications($id_user, $limit);
    }

    public static function updateStatusNotification($source_id){
        return ProspectosNotificationsRep::updateStatusNotification($source_id);
    }

    public static function postSettingNotificationAdmin($params){
        return ProspectosNotificationsRep::postSettingNotificationAdmin($params);
    }

    public static function postSettingNotificationColaborador($params){
        return ProspectosNotificationsRep::postSettingNotificationColaborador($params);
    }

    public static function getSettingNotificationColaborador($params){
        $settingNotification = ProspectosNotificationsRep::getSettingNotificationColaborador($params);
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
        $settings = ProspectosNotificationsRep::getSettingNotificationAdministrador($params);
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

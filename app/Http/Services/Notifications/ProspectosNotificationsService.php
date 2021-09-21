<?php

namespace App\Http\Services\Notifications;

use App\Http\Repositories\Notifications\ProspectosNotificationsRep;
use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
use App\Http\Services\SettingsUserNotifications\SettingsUserNotificationsService;
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
        if (UtilService::verifyNewStatusInStatuses($new_status, $statuses)) {
            ProspectosNotificationsRep::changeStatusforExisitingProspectoNotification($prospecto_id, $new_status);
        }
    }

    public static function sendNotifications()
    {
        $notifications       = ProspectosNotificationsRep::getExisitingProspectosNotifications();
        $prospectos          = ProspectosNotificationsService::getProspectosToSendNotifications();
        $max_time_inactivity = SettingsService::getProspectosMaxTimeInactivity();
        // print_r($prospectos); die();

        UtilService::createCustomLog("sendNotifications_log", "<!-- sendNotifications -->");

        if (count($prospectos) > 0) {
            //Delete no longer inactive prospectos from array
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    foreach ($prospectos as $index => $prospecto) {
                        //Get last update for status prospecto
                        // print_r($prospecto); die();
                        if($prospecto['id_prospecto'] == $notification['source_id']){
                            $inactivity_period = ProspectosNotificationsRep::verifyActivityforProspecto($notification['source_id'], $notification['updated_at']);
                            // print_r($inactivity_period);
                            if ($inactivity_period <= 0) {
                                unset($prospectos[$index]);
                                ProspectosNotificationsService::changeStatusforExisitingProspectoNotification($notification['source_id'], 'resuelto');
                                UtilService::createCustomLog("sendNotifications_log", "| line 65 | changeStatusforExisitingProspectoNotification for notification -> " . $notification['source_id'] . " resuelto");
                            }
                        }
                    }
                }
            }

            $max_time_inactivity =  SettingsService::getProspectosMaxTimeInactivity();
            //Get System settings to check if admins want to receive an email notification
            $send_emails = SettingsService::getProspectosSendInactivityEmailForAdmins();
            
            if ($send_emails == "all") {
                $admins = ProspectosNotificationsService::getAdminsToSendProspectosNotificationEscalation(3);
                //  print_r($admins);
            }else{
                $admins = [];
            }
           

            foreach ($prospectos as $key => $prospecto) {
                $existing_notification  = ProspectosNotificationsRep::checkProspectoNotification($prospecto['id_prospecto']);
                $inactivity_period      = 0;
                if (isset($existing_notification->id)) {
                    $inactivity_period              = UtilService::getHoursDifferenceForTimeStamps($existing_notification->updated_at, date('Y-m-d H:i:s'));
                    $new_inactivity_period          = $existing_notification->inactivity_period + $inactivity_period;
                    $prospecto['inactivity_period'] = $new_inactivity_period;

                    if ($new_inactivity_period >= ($max_time_inactivity  * ($existing_notification->attempts + 1))) {
                        $prospecto['attempts']      = $existing_notification->attempts + 1;
                        ProspectosNotificationsRep::updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto['id_prospecto'], $prospecto['inactivity_period'], true);
                        UtilService::createCustomLog("sendNotifications_log", "| line 95 | updateAttemptsAndInactivityforExisitingProspectoNotification for prospecto -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['inactivity_period']);
                        SendNotificationService::sendInactiveProspectNotification($prospecto);
                        UtilService::createCustomLog("sendNotifications_log", "| line 97 | sendInactiveProspectNotification for prospecto colaborador -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                        if ($send_emails == "all") {
                            if (count($admins) > 0) {
                                $prospecto_for_admin = $prospecto;
                                foreach ($admins as $key_2 => $admin) {
                                    $prospecto_for_admin['email']             = $admin['email'];
                                    $prospecto_for_admin['colaborador_id']    = $admin['id'];
                                    SendNotificationService::sendInactiveProspectNotification($prospecto_for_admin);
                                    UtilService::createCustomLog("sendNotifications_log", "| line 105 | sendInactiveProspectNotification for prospecto admin -> " . $prospecto_for_admin['id_prospecto'] . " -> " . $prospecto_for_admin['colaborador_id']);
                                }
                            }
                        }
                    } else {
                        $prospecto['attempts'] = 1;
                    }
                    $existing_notification_attempts = $existing_notification->attempts;
                } else {
                    $prospecto['attempts']            = 1;
                    $prospecto['inactivity_period']   = $max_time_inactivity;
                    $existing_notification_attempts   = 0;
                    SendNotificationService::sendInactiveProspectNotification($prospecto);
                    UtilService::createCustomLog("sendNotifications_log", "| line 118 | sendInactiveProspectNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                    ProspectosNotificationsRep::createProspectoNotification($prospecto);
                    UtilService::createCustomLog("sendNotifications_log", "| line 120 | createProspectoNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);

                    if ($send_emails == "all") {
                        if (count($admins) > 0) {
                            $prospecto_for_admin = $prospecto;
                            foreach ($admins as $key_2 => $admin) {
                                $prospecto_for_admin['email']             = $admin['email'];
                                $prospecto_for_admin['colaborador_id']    = $admin['id'];
                                SendNotificationService::sendInactiveProspectNotification($prospecto_for_admin);
                                UtilService::createCustomLog("sendNotifications_log", "| line 129 | sendInactiveProspectNotification for prospecto admin -> " . $prospecto_for_admin['id_prospecto'] . " -> " . $prospecto_for_admin['colaborador_id']);
                                ProspectosNotificationsRep::createProspectoNotification($prospecto_for_admin, true);
                                UtilService::createCustomLog("sendNotifications_log", "| line 131 | createProspectoNotification for prospecto admin -> " . $prospecto_for_admin['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                            }
                        }
                    }
                }

                //Get User's settings to check if they want to receive an email notification
                $user_settings = SettingsUserNotificationsService::getSettingNotificationColaborador($prospecto['colaborador_id']);
                if (empty($user_settings) or  (isset($user_settings->configuraciones->disable_email_notification_prospectos) and $user_settings->configuraciones->disable_email_notification_prospectos == 0)) {
                    if ($prospecto['inactivity_period'] > 0 and $prospecto['inactivity_period'] >= ($max_time_inactivity * $prospecto['attempts']) and ($existing_notification_attempts != $prospecto['attempts'])) {
                        ProspectosNotificationsService::sendProspectoNotificationEmail($prospecto);
                        UtilService::createCustomLog("sendNotifications_log", "| line 142 | sendProspectoNotificationEmail for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                    }
                }

                if ($send_emails == "all") {
                    if (count($admins) > 0) {
                        $prospecto_for_admin = $prospecto;
                        foreach ($admins as $key_2 => $admin) {
                            $prospecto_for_admin['email'] = $admin['email'];
                            if ($prospecto_for_admin['inactivity_period'] > 0 and $prospecto_for_admin['inactivity_period'] >= ($max_time_inactivity * $prospecto_for_admin['attempts']) and ($existing_notification_attempts != $prospecto['attempts'])) {
                                ProspectosNotificationsService::sendProspectoNotificationEmail($prospecto_for_admin);
                                UtilService::createCustomLog("sendNotifications_log", "| line 153 | sendProspectoNotificationEmail for prospecto admin -> " . $prospecto_for_admin['id_prospecto'] . " -> " . $prospecto_for_admin['colaborador_id']);
                            }
                        }
                    }
                }
            }
        } else {
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    //Get last update for status prospecto
                    $inactivity_period = ProspectosNotificationsRep::verifyActivityforProspecto($notification['source_id'], $notification['updated_at']);
                    if ($inactivity_period <= 0) {
                        ProspectosNotificationsService::changeStatusforExisitingProspectoNotification($notification['source_id'], 'resuelto');
                        UtilService::createCustomLog("sendNotifications_log", "| line 166 | changeStatusforExisitingProspectoNotification for prospecto  -> " . $notification['source_id'] . " -> resuelto");
                    }
                }
            }
        }
        UtilService::createCustomLog("sendNotifications_log", "<!-- sendNotifications -->");
        UtilService::createCustomLog("sendNotifications_log", " ");
    }

    public static function sendProspectoNotificationEmail($prospecto)
    {
        $msg = array(
            'subject'            => 'Prospecto ' . $prospecto['nombre_prospecto'] . ' sin actividad',
            'email'              => $prospecto['email'],
            'colaborador'        => $prospecto['nombre'] . ' ' . $prospecto['apellido'],
            'nombre_prospecto'   => $prospecto['nombre_prospecto'],
            'attempt'            => $prospecto['attempts'],
            'inactivity_period'  => $prospecto['inactivity_period'],
            'id_prospecto'       => $prospecto['id_prospecto']
        );

        Mailgun::send('mailing.inactivity_prospecto', ['msg' => $msg], function ($m) use ($msg) {
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }

    public static function escalateNotifications()
    {
        $notifications = ProspectosNotificationsService::getProspectosToEscalateForAdmin();
        // print_r($notifications);

        if (count($notifications) > 0) {
            $admins = ProspectosNotificationsService::getAdminsToSendProspectosNotificationEscalation(3);
            // print_r($admins);
            if (count($admins) > 0) {
                foreach ($notifications as $key => $notification) {
                    // print_r($notification);
                    ProspectosNotificationsRep::changeStatusforExisitingProspectoNotification($notification['source_id'], 'escalado');
                    ProspectosNotificationsService::sendProspectoEscalationEmail($notification, $admins);
                }
            }
        }
    }

    public static function sendProspectoEscalationEmail($notification, $admins)
    {
        // print_r($admins);
        foreach ($admins as $key => $admin) {
            $settingsAdmin = SettingsUserNotificationsService::getSettingsNotificationUser($admin["id"]);
            // print_r($settingsAdmin);
            //     print_r($settingsAdmin["settingProspecto"]);
            if ($settingsAdmin["settingProspecto"] == '') {

                $msg = array(
                    'subject'            => 'Escalamiento de Prospecto ' . $notification['nombre_prospecto'] . ' por inactividad',
                    'email'              => $admin['email'],
                    'colaborador'        => $admin['nombre'] . ' ' . $admin['apellido'],
                    'nombre_prospecto'   => $notification['nombre_prospecto'],
                    'attempt'            => $notification['attempts'],
                    'inactivity_period'  => $notification['inactivity_period'],
                    'id_prospecto'       => $notification['source_id'],
                    'admin'              => $admin['nombre'] . ' ' . $admin['apellido'],
                );

                Mailgun::send('mailing.inactivity_escaleted_prospecto', ['msg' => $msg], function ($m) use ($msg) {
                    $m->to($msg['email'], $msg['admin'])->subject($msg['subject']);
                    $m->from('notificaciones@kiper.com.mx', 'Kiper');
                });
            }
        }
    }

    public static function getAdminsToSendProspectosNotificationEscalation($role_id)
    {
        return UsersRep::getUsersByRoleId($role_id);
    }

    public static function getCountNotifications($id_user)
    {
        return ProspectosNotificationsRep::getCountNotifications($id_user);
    }

    public static function getProspectosNotifications($id_user, $limit)
    {
        return ProspectosNotificationsRep::getProspectosNotifications($id_user, $limit);
    }

    public static function getCountProspectosNotifications($id_user)
    {
        return ProspectosNotificationsRep::getCountProspectosNotifications($id_user);
    }

    public static function getCountOportunidadesNotifications($id_user)
    {
        return ProspectosNotificationsRep::getCountOportunidadesNotifications($id_user);
    }

    public static function getOportunidadesNotifications($id_user, $limit)
    {
        return ProspectosNotificationsRep::getOportunidadesNotifications($id_user, $limit);
    }

    public static function updateStatusNotification($source_id)
    {
        return ProspectosNotificationsRep::updateStatusNotification($source_id);
    }

    public static function postSettingNotificationAdmin($params)
    {
        return ProspectosNotificationsRep::postSettingNotificationAdmin($params);
    }

    public static function postSettingNotificationColaborador($params)
    {
        return ProspectosNotificationsRep::postSettingNotificationColaborador($params);
    }

    public static function getSettingNotificationColaborador($params)
    {
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

    public static function getSettingNotificationAdministrador($params)
    {
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

    /*
    | Send-Notifications-Using-User-Settings
    */

    public static function sendNotificationsUsingUserSettings()
    {
        $users_with_settings = SettingsUserNotificationsService::getUsersWithSettings();
        // print_r($users_with_settings);
        if (count($users_with_settings) > 0) {
            UtilService::createCustomLog("sendNotifications_log", "<!-- sendNotificationsUsingUserSettings -->");
            foreach ($users_with_settings as $key => $user_with_settings) {
                $user_settings = SettingsUserNotificationsService::getSettingNotificationColaborador($user_with_settings->id_user);
                if (isset($user_settings->configuraciones->prospectos_max_time_inactivity) and $user_settings->configuraciones->prospectos_max_time_inactivity > 0) {
                    $hours = UtilService::getValueInHours($user_settings->configuraciones->prospectos_max_time_inactivity);
                    // print($hours);
                    $start_date = UtilService::getStartDateForNotifications($hours);
                    // print($start_date);
                    $prospectos = ProspectosNotificationsRep::getProspectosByColaboradorToSendNotifications($user_settings->id_user, $start_date);

                    if (count($prospectos) > 0) {
                        // print_r($prospectos); die();
                        foreach ($prospectos as $key => $prospecto) {
                            //Notification
                            $existing_notification  = ProspectosNotificationsRep::checkProspectoNotification($prospecto['id_prospecto']);
                            $inactivity_period      = 0;
                            if (isset($existing_notification->id)) {
                                $inactivity_period              = UtilService::getHoursDifferenceForTimeStamps($existing_notification->updated_at, date('Y-m-d H:i:s'));
                                $new_inactivity_period          = $existing_notification->inactivity_period + $inactivity_period;
                                $prospecto['inactivity_period'] = $new_inactivity_period;
                                $prospecto['attempts']          = $existing_notification->attempts;
                                // print_r($prospecto); die();
                                if( $new_inactivity_period > $existing_notification->inactivity_period ){
                                    SendNotificationService::sendInactiveProspectNotification($prospecto);
                                    UtilService::createCustomLog("sendNotifications_log", "| line 386 | sendInactiveProspectNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                                    ProspectosNotificationsRep::updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto['id_prospecto'], $new_inactivity_period, NULL, 'no-leido');
                                    UtilService::createCustomLog("sendNotifications_log", "| line 388 | updateAttemptsAndInactivityforExisitingProspectoNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                                }
                            } else {
                                $prospecto['attempts']            = 0;
                                $prospecto['inactivity_period']   = $hours;
                                $inactivity_period                = $hours;
                                SendNotificationService::sendInactiveProspectNotification($prospecto);
                                UtilService::createCustomLog("sendNotifications_log", "| line 395 | sendInactiveProspectNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                                ProspectosNotificationsRep::createProspectoNotification($prospecto);
                                UtilService::createCustomLog("sendNotifications_log", "| line 397 | createProspectoNotification for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                            }
                            //Email notification
                            // print_r($prospecto);
                            if (isset($user_settings->configuraciones->disable_email_notification_prospectos) and !$user_settings->configuraciones->disable_email_notification_prospectos) {
                                $attempts = ($prospecto['attempts'] > 0) ? $prospecto['attempts'] : 1;
                                //Do not send too much emails 
                                if ($inactivity_period > 0 and $inactivity_period >= ($hours * $attempts)) {
                                    ProspectosNotificationsService::sendProspectoNotificationColaboradorEmail($prospecto);
                                    UtilService::createCustomLog("sendNotifications_log", "| line 406 | sendProspectoNotificationColaboradorEmail for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                                }
                                //First notification
                                if ($prospecto['attempts'] == 0 AND $inactivity_period >= ($hours * $attempts)) {
                                    ProspectosNotificationsService::sendProspectoNotificationColaboradorEmail($prospecto);
                                    UtilService::createCustomLog("sendNotifications_log", "| line 411 | sendProspectoNotificationColaboradorEmail for prospecto user -> " . $prospecto['id_prospecto'] . " -> " . $prospecto['colaborador_id']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getProspectosByColaboradorToSendNotifications($user_id, $start_date)
    {
        return ProspectosNotificationsRep::getProspectosByColaboradorToSendNotifications($user_id, $start_date);
    }

    public static function sendProspectoNotificationColaboradorEmail($prospecto)
    {
        $msg = array(
            'subject'            => 'Prospecto ' . $prospecto['nombre_prospecto'] . ' sin actividad',
            'email'              => $prospecto['email'],
            'colaborador'        => $prospecto['nombre'] . ' ' . $prospecto['apellido'],
            'nombre_prospecto'   => $prospecto['nombre_prospecto'],
            'inactivity_period'  => $prospecto['inactivity_period'],
            'id_prospecto'       => $prospecto['id_prospecto']
        );

        Mailgun::send('mailing.inactivity_prospecto_colaborador', ['msg' => $msg], function ($m) use ($msg) {
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }
}

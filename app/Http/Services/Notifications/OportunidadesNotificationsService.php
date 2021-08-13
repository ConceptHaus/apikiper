<?php

namespace App\Http\Services\Notifications;

use App\Http\Repositories\Notifications\OportunidadesNotificationsRep;
use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
use App\Http\Services\SettingsUserNotifications\SettingsUserNotificationsService;
use App\Http\Services\Notifications\SendNotificationService;
use Mailgun;

class OportunidadesNotificationsService
{
    public static function getOportunidadesToSendNotifications()
    {
        $max_time_inactivity = SettingsService::getOportunidadesMaxTimeInactivity();
        $start_date          = UtilService::getStartDateForNotifications($max_time_inactivity);

        return OportunidadesNotificationsRep::getOportunidadesToSendNotifications($start_date);
    }

    public static function getOportunidadesToEscalateForAdmin()
    {
        $max_notification_attempts = SettingsService::getOportunidadesMaxNotificationAttempts();
        return OportunidadesNotificationsRep::getOportunidadesToEscalateForAdmin($max_notification_attempts);
    }

    public static function increaseAttemptsforExisitingOportunidadNotification($oportunidad_id)
    {
        return OportunidadesNotificationsRep::increaseAttemptsforExisitingNotification($oportunidad_id);
    }

    public static function changeStatusforExisitingOportunidadNotification($oportunidad_id, $new_status)
    {
        $statuses = UtilService::getColumnStatuses('notifications', 'status');
        if (UtilService::verifyNewStatusInStatuses($new_status, $statuses)) {
            OportunidadesNotificationsRep::changeStatusforExisitingOportunidadNotification($oportunidad_id, $new_status);
        }
    }

    public static function sendNotifications()
    {
        $notifications       = OportunidadesNotificationsRep::getExisitingOportunidadesNotifications();
        $oportunidades       = OportunidadesNotificationsService::getOportunidadesToSendNotifications();
        $max_time_inactivity = SettingsService::getOportunidadesMaxTimeInactivity();
        // print_r($oportunidades); die();

        if (count($oportunidades) > 0) {
            //Delete no longer inactive oportunidades from array
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    // print_r($notification);
                    foreach ($oportunidades as $index => $oportunidad) {
                        //Get last update for status oportunidad
                        $inactivity_period = OportunidadesNotificationsRep::verifyActivityforOportunidad($notification['source_id'], $notification['updated_at']);
                        // print_r($inactivity_period);
                        if (!in_array($notification['source_id'], $oportunidad)) {
                            if ($inactivity_period <= 0) {
                                OportunidadesNotificationsService::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'resuelto');
                            }
                        } else {
                            if ($inactivity_period <= 0) {
                                unset($oportunidades[$index]);
                                OportunidadesNotificationsService::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'resuelto');
                            }
                        }
                    }
                }
            }

            $max_time_inactivity =  SettingsService::getOportunidadesMaxTimeInactivity();

            foreach ($oportunidades as $key => $oportunidad) {
                $existing_notification  = OportunidadesNotificationsRep::checkOportunidadNotification($oportunidad['id_oportunidad']);
                $inactivity_period      = 0;
                if (isset($existing_notification->id)) {
                    $inactivity_period                  = UtilService::getHoursDifferenceForTimeStamps($existing_notification->updated_at, date('Y-m-d H:i:s'));
                    $new_inactivity_period              = $existing_notification->inactivity_period + $inactivity_period;
                    $oportunidad['inactivity_period']   = $new_inactivity_period;
                    if ($new_inactivity_period > ($max_time_inactivity * ($existing_notification->attempts + 1))) {
                        $oportunidad['attempts']        = $existing_notification->attempts + 1;
                        SendNotificationService::sendInactiveOportunityNotification($oportunidad);
                        OportunidadesNotificationsRep::updateAttemptsAndInactivityforExisitingOportunidadNotification($oportunidad['id_oportunidad'], $oportunidad['inactivity_period'], true);
                    } else {
                        $oportunidad['attempts'] = 1;
                    }
                    $existing_notification_attempts = $existing_notification->attempts;
                } else {
                    $oportunidad['attempts']            = 1;
                    $oportunidad['inactivity_period']   = $max_time_inactivity;
                    $existing_notification_attempts     = 0;
                    SendNotificationService::sendInactiveOportunityNotification($oportunidad);
                    OportunidadesNotificationsRep::createOportunidadNotification($oportunidad);
                }
                // print_r($oportunidad);

                //Get User's settings to check if they want to receive an email notification
                $user_settings = SettingsUserNotificationsService::getSettingNotificationColaborador($oportunidad['colaborador_id']);
                if (empty($user_settings) or  (isset($user_settings->configuraciones->disable_email_notification_oportunidades) and $user_settings->configuraciones->disable_email_notification_oportunidades == 0)) {
                    if ($oportunidad['inactivity_period'] > 0 and $oportunidad['inactivity_period'] >= ($max_time_inactivity * $oportunidad['attempts']) and ($existing_notification_attempts != $oportunidad['attempts'])) {
                        OportunidadesNotificationsService::sendOportunidadNotificationEmail($oportunidad);
                    }
                }

                //Get System settings to check if admins want to receive an email notification
                $send_emails = SettingsService::getOportunidadesSendInactivityEmailForAdmins();
                if ($send_emails == "all") {
                    $admins = OportunidadesNotificationsService::getAdminsToSendOportunidadNotificationEscalation(3);
                    //  print_r($admins);
                    if (count($admins) > 0) {
                        $oportunidad_for_admin = $oportunidad;
                        foreach ($admins as $key_2 => $admin) {
                            $oportunidad_for_admin['email'] = $admin['email'];
                            if ($oportunidad_for_admin['inactivity_period'] > 0 and $oportunidad_for_admin['inactivity_period'] >= ($max_time_inactivity * $oportunidad_for_admin['attempts']) and ($existing_notification_attempts != $oportunidad_for_admin['attempts'])) {
                                OportunidadesNotificationsService::sendOportunidadNotificationEmail($oportunidad_for_admin);
                            }
                        }
                    }
                }
            }
        } else {
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    //Get last update for status oportunidad
                    $inactivity_period = OportunidadesNotificationsRep::verifyActivityforOportunidad($notification['source_id'], $notification['updated_at']);
                    if ($inactivity_period <= 0) {
                        OportunidadesNotificationsService::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'resuelto');
                    }
                }
            }
        }
    }

    public static function sendOportunidadNotificationEmail($oportunidad)
    {
        $msg = array(
            'subject'            => 'Oportunidad ' . $oportunidad['nombre_oportunidad'] . ' sin actividad',
            'email'              => $oportunidad['email'],
            'colaborador'        => $oportunidad['nombre'] . ' ' . $oportunidad['apellido'],
            'nombre_oportunidad' => $oportunidad['nombre_oportunidad'],
            'attempt'            => $oportunidad['attempts'],
            'inactivity_period'  => $oportunidad['inactivity_period'],
            'id_oportunidad'     => $oportunidad['id_oportunidad']
        );

        Mailgun::send('mailing.inactivity_oportunidad', ['msg' => $msg], function ($m) use ($msg) {
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }

    public static function escalateNotifications()
    {
        $notifications = OportunidadesNotificationsService::getOportunidadesToEscalateForAdmin();
        // print_r($notifications);

        if (count($notifications) > 0) {
            $admins = OportunidadesNotificationsService::getAdminsToSendOportunidadNotificationEscalation(3);
            //  print_r($admins);
            if (count($admins) > 0) {
                foreach ($notifications as $key => $notification) {
                    OportunidadesNotificationsRep::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'escalado');
                    OportunidadesNotificationsService::sendOportunidadEscalationEmail($notification, $admins);
                }
            }
        }
    }

    public static function sendOportunidadEscalationEmail($notification, $admins)
    {
        foreach ($admins as $key => $admin) {
            $msg = array(
                'subject'            => 'Escalamiento de Oportunidad ' . $notification['nombre_oportunidad'] . ' por inactividad',
                'email'              => $admin['email'],
                'colaborador'        => $admin['nombre'] . ' ' . $admin['apellido'],
                'nombre_oportunidad' => $notification['nombre_oportunidad'],
                'attempt'            => $notification['attempts'],
                'inactivity_period'  => $notification['inactivity_period'],
                'id_oportunidad'     => $notification['source_id'],
                'admin'              => $admin['nombre'] . ' ' . $admin['apellido'],
            );

            Mailgun::send('mailing.inactivity_escaleted_oportunidad', ['msg' => $msg], function ($m) use ($msg) {
                $m->to($msg['email'], $msg['admin'])->subject($msg['subject']);
                $m->from('notificaciones@kiper.com.mx', 'Kiper');
            });
        }
    }

    public static function getAdminsToSendOportunidadNotificationEscalation($role_id)
    {
        return UsersRep::getUsersByRoleId($role_id);
    }

    /*
    | Send-Notifications-Using-User-Settings
    */

    public static function sendNotificationsUsingUserSettings()
    {
        $users_with_settings = SettingsUserNotificationsService::getUsersWithSettings();
        // print_r($users_with_settings);
        if (count($users_with_settings) > 0) {
            foreach ($users_with_settings as $key => $user_with_settings) {
                $user_settings = SettingsUserNotificationsService::getSettingNotificationColaborador($user_with_settings->id_user);
                if (isset($user_settings->configuraciones->oportunidades_max_time_inactivity) and $user_settings->configuraciones->oportunidades_max_time_inactivity > 0) {
                    $hours = UtilService::getValueInHours($user_settings->configuraciones->oportunidades_max_time_inactivity);
                    // print($hours);
                    $start_date = UtilService::getStartDateForNotifications($hours);
                    // print($start_date);
                    $oportunidades = OportunidadesNotificationsService::getOportunidadesByColaboradorToSendNotifications($user_settings->id_user, $start_date);
                    // print_r($oportunidades);
                    if (count($oportunidades) > 0) {
                        // print_r($oportunidades);
                        foreach ($oportunidades as $key => $oportunidad) {
                            //Notification
                            $existing_notification  = OportunidadesNotificationsRep::checkOportunidadNotification($oportunidad['id_oportunidad']);
                            $inactivity_period      = 0;
                            if (isset($existing_notification->id)) {
                                $inactivity_period                  = UtilService::getHoursDifferenceForTimeStamps($existing_notification->updated_at, date('Y-m-d H:i:s'));
                                $new_inactivity_period              = $existing_notification->inactivity_period + $inactivity_period;
                                $oportunidad['inactivity_period']   = $new_inactivity_period;
                                $oportunidad['attempts']            = $existing_notification->attempts;
                                SendNotificationService::sendInactiveOportunityNotification($oportunidad);
                                OportunidadesNotificationsRep::updateAttemptsAndInactivityforExisitingOportunidadNotification($oportunidad['id_oportunidad'], $new_inactivity_period, NULL, 'no-leido');
                            } else {
                                $oportunidad['attempts']            = 0;
                                $oportunidad['inactivity_period']   = $hours;
                                $inactivity_period                  = $hours;
                                SendNotificationService::sendInactiveOportunityNotification($oportunidad);
                                OportunidadesNotificationsRep::createOportunidadNotification($oportunidad);
                            }

                            //Email notification
                            if (isset($user_settings->configuraciones->disable_email_notification_oportunidades) and !$user_settings->configuraciones->disable_email_notification_oportunidades) {
                                // print_r($oportunidad);
                                $attempts = ($oportunidad['attempts'] > 0) ? $oportunidad['attempts'] : 1;
                                if ($inactivity_period > 0 AND $inactivity_period > ($hours * $attempts)) {
                                    OportunidadesNotificationsService::sendOportunidadNotificationColaboradorEmail($oportunidad);
                                }
                                //First notification
                                if ($oportunidad['attempts'] == 0 AND $inactivity_period >= ($hours * $attempts)) {
                                    OportunidadesNotificationsService::sendOportunidadNotificationColaboradorEmail($oportunidad);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getOportunidadesByColaboradorToSendNotifications($user_id, $start_date)
    {
        return OportunidadesNotificationsRep::getOportunidadesByColaboradorToSendNotifications($user_id, $start_date);
    }

    public static function sendOportunidadNotificationColaboradorEmail($oportunidad)
    {
        $msg = array(
            'subject'            => 'Oportunidad ' . $oportunidad['nombre_oportunidad'] . ' sin actividad',
            'email'              => $oportunidad['email'],
            'colaborador'        => $oportunidad['nombre'] . ' ' . $oportunidad['apellido'],
            'nombre_oportunidad' => $oportunidad['nombre_oportunidad'],
            'inactivity_period'  => $oportunidad['inactivity_period'],
            'id_oportunidad'     => $oportunidad['id_oportunidad']
        );

        Mailgun::send('mailing.inactivity_oportunidad_colaborador', ['msg' => $msg], function ($m) use ($msg) {
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }
}

<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\OportunidadesNotificationsRep;
use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
use App\Http\Services\SettingsUserNotifications\SettingsUserNotificationsService;
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
        if(UtilService::verifyNewStatusInStatuses( $new_status, $statuses))
        {
            OportunidadesNotificationsRep::changeStatusforExisitingOportunidadNotification($oportunidad_id, $new_status);
        }
    }

    public static function sendNotifications()
    {
        $notifications = OportunidadesNotificationsRep::getExisitingOportunidadesNotifications();
        $oportunidades = OportunidadesNotificationsService::getOportunidadesToSendNotifications();
        
        if (count($oportunidades) > 0) {
            //Delete no longer inactive oportunidades from array
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    foreach ($oportunidades as $index => $oportunidad) {
                        if (!in_array($notification['source_id'], $oportunidad)) {
                           unset($oportunidades[$index]);
                           OportunidadesNotificationsService::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'resuelto');
                        }
                    }
                }
            }

            $max_time_inactivity =  SettingsService::getOportunidadesMaxTimeInactivity();

            foreach ($oportunidades as $key => $oportunidad) {
                $existing_notification = OportunidadesNotificationsRep::checkOportunidadNotification($oportunidad['id_oportunidad']);
                if(isset($existing_notification->id)){
                    OportunidadesNotificationsRep::updateAttemptsAndInactivityforExisitingOportunidadNotification($oportunidad['id_oportunidad']);
                    $oportunidad['attempts']            = $existing_notification->attempts;
                    $oportunidad['inactivity_period']   = $existing_notification->inactivity_period;
                    
                }else{
                    $oportunidad['attempts']            = 1;
                    $oportunidad['inactivity_period']   = $max_time_inactivity; 
                    OportunidadesNotificationsRep::createOportunidadNotification($oportunidad);
                }
                // print_r($oportunidad);
                //Get User's settings to check if they want to receive an email notification
                $user_settings = SettingsUserNotificationsService::getSettingNotificationColaborador($oportunidad['colaborador_id']);
                if (isset($user_settings->configuraciones->disable_email_notification_oportunidades) AND $user_settings->configuraciones->disable_email_notification_oportunidades == 0) {
                    OportunidadesNotificationsService::sendOportunidadNotificationEmail($oportunidad);
                }
            }
        }else{
            if (count($notifications) > 0) {
                foreach ($notifications as $key => $notification) {
                    OportunidadesNotificationsService::changeStatusforExisitingOportunidadNotification($notification['source_id'], 'resuelto');    
                }
            }    
        }
    }

    public static function sendOportunidadNotificationEmail($oportunidad)
    {
        $msg = array(
                    'subject'            => 'Oportunidad '.$oportunidad['nombre_oportunidad'].' sin actividad',
                    'email'              => $oportunidad['email'],
                    'colaborador'        => $oportunidad['nombre'].' '.$oportunidad['apellido'],
                    'nombre_oportunidad' => $oportunidad['nombre_oportunidad'],
                    'attempt'            => $oportunidad['attempts'],
                    'inactivity_period'  => $oportunidad['inactivity_period'],
                    'id_oportunidad'     => $oportunidad['id_oportunidad']
                );

        Mailgun::send('mailing.inactivity_oportunidad', ['msg' => $msg], function ($m) use ($msg){
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
        });
    }

    public static function escalateNotifications()
    {
        $notifications = OportunidadesNotificationsService::getOportunidadesToEscalateForAdmin();
        // print_r($notifications);
        
        if (count($notifications) > 0) {
            $admins = OportunidadesNotificationsService::getAdminsToSendoportunidadNotificationEscalation(3);
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
                'subject'            => 'Escalamiento de Oportunidad '.$notification['nombre_oportunidad'].' por inactividad',
                'email'              => $admin['email'],
                'colaborador'        => $admin['nombre'].' '.$admin['apellido'],
                'nombre_oportunidad' => $notification['nombre_oportunidad'],
                'attempt'            => $notification['attempts'],
                'inactivity_period'  => $notification['inactivity_period'],
                'id_oportunidad'     => $notification['source_id'],
                'admin'              => $admin['nombre'].' '.$admin['apellido'],
            );

            Mailgun::send('mailing.inactivity_escaleted_oportunidad', ['msg' => $msg], function ($m) use ($msg){
                $m->to($msg['email'], $msg['admin'])->subject($msg['subject']);
                $m->from('notificaciones@kiper.com.mx', 'Kiper');
            });
        }  
    }

    public static function getAdminsToSendoportunidadNotificationEscalation($role_id)
    {
        return UsersRep::getUsersByRoleId($role_id);    
    }
    
}

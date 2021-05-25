<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\ProspectosNotificationsRep;
use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
use Mailgun;

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
        return ProspectosNotificationsRep::increaseAttemptsforExisitingNotification($prospecto_id);
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

}

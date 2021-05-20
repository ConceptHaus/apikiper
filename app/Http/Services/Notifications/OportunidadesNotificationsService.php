<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\OportunidadesNotificationsRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;
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
                OportunidadesNotificationsService::sendOportunidadNotificationEmail($oportunidad);
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
    
}

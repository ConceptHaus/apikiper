<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\OportunidadesNotificationsRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;

class OportunidadesNotificationsService
{
    public static function getOportunidadesToSendNotifications()
    {
        $max_time_inactivity = SettingsService::getOportunidadesMaxTimeInactivity();
        $start_date          = OportunidadesNotificationsService::getTimeStampAsStartDate($max_time_inactivity);

        return OportunidadesNotificationsRep::getOportunidadesToSendNotifications($start_date);
    }

    public static function getOportunidadesToEscalateForAdmin()
    {
       
        $max_notification_attempts = SettingsService::getOportunidadesMaxNotificationAttempts();
        return OportunidadesNotificationsRep::getOportunidadesToEscalateForAdmin($max_notification_attempts);
    }

    public static function increaseAttemptsforExisitingNotification($oportunidad_id)
    {
        return OportunidadesNotificationsRep::increaseAttemptsforExisitingNotification($oportunidad_id);
    }

    public static function changeStatusforExisitingNotification($oportunidad_id, $new_status)
    {
        $statuses = UtilService::getColumnStatuses('notifications', 'status'); 
        if(OportunidadesNotificationsService::verifyNewStatusInStatuses( $new_status, $statuses))
        {
            OportunidadesNotificationsRep::changeStatusforExisitingNotification($oportunidad_id, $new_status);
        }
    }

    public static function getTimeStampAsStartDate($hours)
    {
        $now        = date('Y-m-d H:i:s');
        $start_date = strtotime('-'.$hours.' hours', strtotime($now));
        $start_date = date('Y-m-d H:i:s', $start_date);

        return $start_date;
    }

    public static function verifyNewStatusInStatuses($new_status, $statuses)
    {
        return in_array($new_status, $statuses);
    }
}

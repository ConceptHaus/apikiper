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
            OportunidadesNotificationsRep::changeStatusforExisitingNotification($oportunidad_id, $new_status);
        }
    }
    
}

<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\ProspectosNotificationsRep;
use App\Http\Services\Settings\SettingsService;
use App\Http\Services\UtilService;

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

    public static function increaseAttemptsforExisitingProspectoNotification($oportunidad_id)
    {
        return ProspectosNotificationsRep::increaseAttemptsforExisitingNotification($oportunidad_id);
    }

    public static function changeStatusforExisitingProspectoNotification($oportunidad_id, $new_status)
    {
        $statuses = UtilService::getColumnStatuses('notifications', 'status'); 
        if(UtilService::verifyNewStatusInStatuses($new_status, $statuses))
        {
            ProspectosNotificationsRep::changeStatusforExisitingNotification($oportunidad_id, $new_status);
        }
    }

}

<?php
namespace App\Http\Services\Notifications;
use App\Http\Repositories\Notifications\ProspectosNotificationsRep;
use App\Http\Services\Settings\SettingsService;

class ProspectosNotificationsService
{
    public static function getProspectosToSendNotifications()
    {
        $max_time_inactivity = SettingsService::getProspectosMaxTimeInactivity();
        $start_date          = ProspectosNotificationsService::getTimeStampAsStartDate($max_time_inactivity);

        return ProspectosNotificationsRep::getProspectosToSendNotifications($start_date);
    }

    public static function insertProspectosToSendNotifications($prospectos){
        return ProspectosNotificationsRep::insertProspectosToSendNotifications($prospectos);
    }

    public static function getTimeStampAsStartDate($hours)
    {
        $now        = date('Y-m-d H:i:s');
        $start_date = strtotime('-'.$hours.' hours', strtotime($now));
        $start_date = date('Y-m-d H:i:s', $start_date);

        return $start_date;
    }
}

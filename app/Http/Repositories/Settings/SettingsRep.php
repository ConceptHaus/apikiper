<?php

namespace App\Http\Repositories\Settings;

use App\Modelos\Setting;

class SettingsRep
{
    /*
    | Oportunidades
    */

    public static function getOportunidadesMaxTimeInactivity()
    {
        $setting = Setting::where('setting', 'oportunidades_max_time_inactivity')->first();

        return $setting->value;
    }

    public static function getOportunidadesMaxNotificationAttempts()
    {
        $setting = Setting::where('setting', 'oportunidades_max_notification_attempt')->first();

        return $setting->value;
    }

    /*
    | Prospectos
    */

    public static function getProspectosMaxTimeInactivity()
    {
        $setting = Setting::where('setting', 'prospectos_max_time_inactivity')->first();

        return $setting->value;
    }

    public static function getProspectosMaxNotificationAttempts()
    {
        $setting = Setting::where('setting', 'prospectos_max_notification_attempt')->first();

        return $setting->value;
    }

    /*
    | Cat Status Oportunidad
    */

    public static function getMaxEstatusOportunidadMaxCount()
    {
        $setting = Setting::where('setting', 'oportunidades_status_max_count')->first();

        return $setting->value;
    }

}

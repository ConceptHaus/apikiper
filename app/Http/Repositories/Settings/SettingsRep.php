<?php

namespace App\Http\Repositories\Settings;

use App\Modelos\Setting;

class SettingsRep
{
    public static function getOportunidadesMaxTimeInactivity()
    {
        $setting = Setting::where('setting', 'oportunidades_max_time_inactivity')->first();

        return $setting->value;
    }

    public static function getOportunidadesNotificationAttempts()
    {
        $setting = Setting::where('setting', 'oportunidades_max_notification_attempt')->first();

        return $setting->value;
    }

}
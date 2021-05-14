<?php
namespace App\Http\Services\Settings;
use App\Http\Repositories\Settings\SettingsRep;

class SettingsService
{
    public static function getOportunidadesMaxTimeInactivity(){
        $setting = SettingsRep::getOportunidadesMaxTimeInactivity();
        return SettingsService::getValueInHours($setting);
    }

    public static function getProspectosMaxTimeInactivity(){
        $setting = SettingsRep::getProspectosMaxTimeInactivity();
        return SettingsService::getValueInHours($setting);
    }

    public static function getValueInHours($value)
    {
        $values = explode("|", $value);
        $hours  = $values[0];
        if ($values[1] == "days") {
            $hours = $values[0] * 24;
        }
        return $hours;
    }
}

<?php
namespace App\Http\Services\Settings;
use App\Http\Repositories\Settings\SettingsRep;
use App\Http\Services\UtilService;

class SettingsService
{
    /*
    | Oportunidades
    */

    public static function getOportunidadesMaxTimeInactivity()
    {
        $setting = SettingsRep::getOportunidadesMaxTimeInactivity();
        return UtilService::getValueInHours($setting);
    }

    public static function getOportunidadesMaxNotificationAttempts()
    {
        return SettingsRep::getOportunidadesMaxNotificationAttempts();   
    }

    /*
    | Prospectos
    */

    public static function getProspectosMaxTimeInactivity()
    {
        $setting = SettingsRep::getProspectosMaxTimeInactivity();
        return UtilService::getValueInHours($setting);
    }

    public static function getProspectosMaxNotificationAttempts()
    {
        return SettingsRep::getProspectosMaxNotificationAttempts();   
    }

    /*
    | Cat Status Oportunidad
    */

    public static function getMaxEstatusOportunidadMaxCount()
    {
        return SettingsRep::getMaxEstatusOportunidadMaxCount();
    }

    /*
    | Inactivity Emails for Admins
    */

    public static function getOportunidadesSendInactivityEmailForAdmins()
    {
        return SettingsRep::getOportunidadesSendInactivityEmailForAdmins();
    }

    public static function getProspectosSendInactivityEmailForAdmins()
    {
        return SettingsRep::getProspectosSendInactivityEmailForAdmins();
    }
    
}

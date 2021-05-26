<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Setting;
use App\Modelos\SettingUserNotification;
use DB;

class ProspectosNotificationsRep
{
    public static function getProspectosToSendNotifications($start_date){
        
        $end_date = date('Y-m-d H:i:s');

        $prospectos = Prospecto::select('prospectos.id_prospecto',
                                        'prospectos.nombre as nombre_prospecto',
                                        'status_prospecto.updated_at',
                                        'detalle_prospecto.telefono',
                                        'cat_status_prospecto.status',
                                        'users.nombre',
                                        'users.apellido',
                                        'users.email',
                                        'users.id as colabrador_id')
                                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('users','colaborador_prospecto.id_colaborador','users.id')
                                ->join('status_prospecto','colaborador_prospecto.id_prospecto','status_prospecto.id_prospecto')
                                ->join('detalle_prospecto','colaborador_prospecto.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                ->where('status_prospecto.updated_at', '<=', $start_date)
                                ->groupBy('prospectos.id_prospecto')
                                ->get()
                                ->toArray();
        
        return $prospectos;
    }

    public static function getProspectosToEscalateForAdmin($max_notification_attempts){
        
        $prospectos =  Notification::select('notifications.id',
                                            'notifications.colaborador_id',
                                            'notifications.source_id',
                                            'notifications.notification_type',
                                            'notifications.status as notification_status',
                                            'notifications.attempts',
                                            'notifications.inactivity_period',
                                            'cat_status_prospecto.status',
                                            'users.nombre',
                                            'users.apellido',
                                            'users.email',
                                            'prospectos.nombre as nombre_prospecto')
                                    ->join('users','notifications.colaborador_id','users.id')
                                    ->join('status_prospecto','notifications.source_id','status_prospecto.id_prospecto')
                                    ->join('detalle_prospecto','notifications.source_id','detalle_prospecto.id_prospecto')
                                    ->join('prospectos','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                                    ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                    ->where('notifications.attempts', '>=', $max_notification_attempts)
                                    ->where('notifications.status', '!=', 'resuelto')
                                    ->get()
                                    ->toArray();
        
        return $prospectos;
    }

    public static function increaseAttemptsforExisitingProspectoNotification($prospecto_id)
    {
        $prospecto  =   Notification::where('source_id', $prospecto_id)
                                    ->where('notification_type', 'prospecto')
                                    ->where('status', '!=', 'resuleto')
                                    ->first();

        if (!empty($prospecto)) {
            $prospecto->attempts = $prospecto->attempts + 1;
            $prospecto->save();
        }
    }

    public static function changeStatusforExisitingProspectoNotification($prospecto_id, $new_status)
    {
        $prospecto = Notification::where('source_id', $prospecto_id)->first();
        
        if (!empty($prospecto)) {
            $prospecto->status = $new_status;
            $prospecto->save();
        }
    }

    public static function createProspectoNotification($prospecto)
    {
        $notificaton                    = new Notification;
        $notificaton->colaborador_id    = $prospecto['colaborador_id'];
        $notificaton->source_id         = $prospecto['id_prospecto'];
        $notificaton->notification_type = 'prospecto';
        $notificaton->inactivity_period = $prospecto['inactivity_period'];
        $notificaton->status            = 'no-leido';
        $notificaton->attempts          = $prospecto['attempts'];
        $notificaton->save();
    }

    public static function checkProspectoNotification($prospecto_id)
    {
        $exisiting_notification = Notification::where('source_id', $prospecto_id)->first();
       
        if (!empty($exisiting_notification)) {
            return $exisiting_notification;
        }else{
            return [];
        }
    }

    public static function updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto_id)
    {
        $prospecto =  Notification::where('source_id', $prospecto_id)
                                    ->where('notification_type', 'prospecto')
                                    ->where('status', '!=', 'resuleto')
                                    ->first();

        if (!empty($prospecto)) {
            $prospecto->attempts          = $prospecto->attempts + 1;
            $prospecto->inactivity_period = $prospecto->inactivity_period + 24;
            $prospecto->save();

            return $prospecto;
        }
    }

    public static function getExisitingProspectosNotifications()
    {
        return  Notification::where('notification_type', 'prospecto')
                            ->where('status', '!=', 'resuleto')
                            ->get()
                            ->toArray();   
    }

    public static function getCountNotifications($id_user){
        return DB::table('notifications')
                ->where('colaborador_id', $id_user)
                ->where('status', 'no-leido')
                ->count();
    }

    public static function getProspectosNotifications($id_user, $limit){
        return Notification::select('notifications.*', 'prospectos.*', 
                            DB::raw('CONCAT(users.nombre," ",users.apellido) as colaborador'),
                            DB::raw("DATEDIFF(now(),notifications.created_at)as days")
                            )
                ->leftjoin('prospectos', 'prospectos.id_prospecto', '=', 'notifications.source_id')
                ->leftjoin('users', 'users.id', '=', 'notifications.colaborador_id')
                ->where('colaborador_id', $id_user)
                ->where('notifications.notification_type', 'prospecto')
                ->orderby('notifications.created_at', 'desc')
                ->take($limit)
                ->get();
    }

    public static function getOportunidadesNotifications($id_user, $limit){
        return Notification::select('notifications.*', 'oportunidades.*', 
                            DB::raw('CONCAT(users.nombre," ",users.apellido) as colaborador'),
                            DB::raw("DATEDIFF(now(),notifications.created_at)as days"),
                            DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as prospecto')
                            )
                ->leftjoin('oportunidades', 'oportunidades.id_oportunidad', '=', 'notifications.source_id')
                ->leftjoin('users', 'users.id', '=', 'notifications.colaborador_id')
                ->leftjoin('oportunidad_prospecto', 'oportunidad_prospecto.id_oportunidad', '=', 'oportunidades.id_oportunidad')
                ->leftjoin('prospectos', 'prospectos.id_prospecto', '=', 'oportunidad_prospecto.id_prospecto')
                ->where('colaborador_id', $id_user)
                ->where('notifications.notification_type', 'oportunidad')
                ->orderby('notifications.created_at', 'desc')
                ->take($limit)
                ->get();
    }

    public static function updateStatusNotification($source_id){
        $status = Notification::where('source_id', $source_id)->first();
        $status->status = 'leido';
        $status->save();
    }

    public static function postSettingNotificationAdmin($params){
        $pros_max_inac = $params["max_time_prospect"]."|".$params["timeP"];
        $opo_max_inac = $params["max_time_oportu"]."|".$params["opor_reciv_inact"];

        $oportunidades_status_max_count = Setting::where('id', 1)->first();
        $oportunidades_status_max_count->value = $params["max_time_attempt_oport"];
        $oportunidades_status_max_count->save();

        $prospectos_max_time_inactivity = Setting::where('id', 2)->first();
        $prospectos_max_time_inactivity->value = $pros_max_inac;
        $prospectos_max_time_inactivity->save();

        $prospectos_max_notification_attempt = Setting::where('id', 3)->first();
        $prospectos_max_notification_attempt->value = $params["max_time_attempt_prospect"];
        $prospectos_max_notification_attempt->save();

        $prospectos_receive_inactivity_notifications = Setting::where('id', 4)->first();
        $prospectos_receive_inactivity_notifications->value = $params["prosp_reciv_inact"];
        $prospectos_receive_inactivity_notifications->save();

        $oportunidades_max_time_inactivity = Setting::where('id', 5)->first();
        $oportunidades_max_time_inactivity->value = $opo_max_inac;
        $oportunidades_max_time_inactivity->save();

        $oportunidades_max_notification_attempt = Setting::where('id', 6)->first();
        $oportunidades_max_notification_attempt->value = $params["max_time_attempt_oport"];
        $oportunidades_max_notification_attempt->save();

        $oportunidades_receive_inactivity_notifications = Setting::where('id', 7)->first();
        $oportunidades_receive_inactivity_notifications->value = $params["oport_reciv_inact"];
        $oportunidades_receive_inactivity_notifications->save();
    }

    

    public static function postSettingNotificationColaborador($params){
        
        if (is_null($params->disable_email_notification_prospectos)) {
            $params->disable_email_notification_prospectos = false;
        }
        if (is_null($params->disable_email_notification_oportunidades)) {
            $params->disable_email_notification_oportunidades = false;
        }
        if (is_null($params->disable_email_notification_prospectos)) {
            $params->disable_email_notification_prospectos = false;
        }
        if (is_null($params->disable_email_notification_escalated_oportunidades)) {
            $params->disable_email_notification_escalated_oportunidades = false;
        }

        $configuraciones = json_encode(array(
            'disable_email_notification_prospectos' => $params->disable_email_notification_prospectos,
            'disable_email_notification_oportunidades' => $params->disable_email_notification_oportunidades,
            'disable_email_notification_escalated_prospectos' => $params->disable_email_notification_escalated_prospectos,
            'disable_email_notification_escalated_oportunidades' => $params->disable_email_notification_escalated_oportunidades,
            'oportunidades_max_time_inactivity' => $params->max_time_oportu_colab.'|'.$params->timeOC,
            'prospectos_max_time_inactivity' => $params->max_time_prospect_colab.'|'.$params->timePC
        ));

        $usuario = SettingUserNotification::where('id_user', $params->idUsers)->first();
        
        if ($usuario) {
            $update = SettingUserNotification::where('id_user', $usuario['id_user'])->first();
            $update->configuraciones = $configuraciones;
            $update->save();
        } else {
            $settingColaborador = new SettingUserNotification;
            $settingColaborador->id_user = $params->idUsers;
            $settingColaborador->configuraciones = $configuraciones;
            $settingColaborador->save();
        }
        
    }

    public static function getSettingNotificationColaborador($params){

       $usuario = SettingUserNotification::where('id_user', $params->id_usuario)->first();
       return $usuario;
        
    }

    public static function getSettingNotificationAdministrador($params){

        return $settings = Setting::all();
         
     }

}

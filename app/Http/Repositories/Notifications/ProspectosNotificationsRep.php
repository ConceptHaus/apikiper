<?php

namespace App\Http\Repositories\Notifications;

use App\Modelos\Notification;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Setting;
use App\Modelos\SettingUserNotification;
use App\Http\Services\UtilService;
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
                                        'users.id as colaborador_id')
                                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('users','colaborador_prospecto.id_colaborador','users.id')
                                ->join('status_prospecto','colaborador_prospecto.id_prospecto','status_prospecto.id_prospecto')
                                ->join('detalle_prospecto','colaborador_prospecto.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                ->where('status_prospecto.updated_at', '<=', $start_date)
                                ->where('status_prospecto.id_cat_status_prospecto', 2)
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
                                    ->where(function($q) {
                                        $q->where('status', '!=', 'resuelto')
                                          ->orWhereNull('status');
                                    })
                                    ->first();

        if (!empty($prospecto)) {
            $prospecto->attempts = $prospecto->attempts + 1;
            $prospecto->save();
        }
    }

    public static function changeStatusforExisitingProspectoNotification($prospecto_id, $new_status)
    {
        $prospectos = Notification::where('source_id', $prospecto_id)->first();
        
        if(count($prospectos) > 0){
            foreach ($prospectos as $key => $prospecto) {
                $prospecto->status = $new_status;
                $prospecto->save();
            }
        }
    }

    public static function createProspectoNotification($prospecto, $for_admin=false)
    {
        $notificaton                    = new Notification;
        $notificaton->colaborador_id    = $prospecto['colaborador_id'];
        $notificaton->source_id         = $prospecto['id_prospecto'];
        $notificaton->notification_type = 'prospecto';
        $notificaton->inactivity_period = $prospecto['inactivity_period'];
        $notificaton->view              = 'no-leido';
        $notificaton->attempts          = $prospecto['attempts'];
        if($for_admin){
            $notificaton->type          = 2;    
        }
        $notificaton->save();
    }

    public static function checkProspectoNotification($prospecto_id)
    {
        $exisiting_notification =   Notification::where('source_id', $prospecto_id)
                                                ->where(function($q) {
                                                    $q->where('status', '!=', 'resuelto')
                                                    ->orWhereNull('status');
                                                })
                                                ->where('type', 1)
                                                ->first();
                                                
        if (!empty($exisiting_notification)) {
            return $exisiting_notification;
        }else{
            return [];
        }
    }

    // public static function updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto_id)
    // {
    //     $prospecto =  Notification::where('source_id', $prospecto_id)
    //                                 ->where('notification_type', 'prospecto')
    //                                 ->where('status', '!=', 'resuleto')
    //                                 ->first();

    //     if (!empty($prospecto)) {
    //         $prospecto->attempts          = $prospecto->attempts + 1;
    //         $prospecto->inactivity_period = $prospecto->inactivity_period + 24;
    //         $prospecto->save();

    //         return $prospecto;
    //     }
    // }

    public static function getExisitingProspectosNotifications()
    {
        return  Notification::where('notification_type', 'prospecto')
                            ->where(function($q) {
                                $q->where('status', '!=', 'resuelto')
                                ->orWhereNull('status');
                            })
                            ->where('type', '!=', 2)
                            ->get()
                            ->toArray();   
    }

    public static function getCountNotifications($id_user){
        return DB::table('notifications')
                ->where('colaborador_id', $id_user)
                ->where('view', 'no-leido')
                ->where(function ($query) {
                    $query->where('status', '!=', 'resuelto');
                    $query->orWhereNull('status');
                })
                ->count();
    }

    public static function getProspectosNotifications($id_user, $limit){        
        return DB::select("select n.*, p.*,
        CONCAT(u.nombre, ' ', u.apellido) as colaborador,
        DATEDIFF(now(),n.created_at)as days
            from notifications n
            left join prospectos p on p.id_prospecto = n.source_id
            left join users u on u.id = n.colaborador_id
            where n.id in (select id from notifications n2
                        where DATEDIFF(NOW(), n2.updated_at) <= 3
                        and n2.view = 'leido'
                        and (n2.status != 'resuelto'
                            or n2.status is null)
                        and n2.notification_type = 'prospecto')
            or ( n.view = 'no-leido'
            or n.status = 'escalado')
            and n.notification_type = 'prospecto'
            and (n.status is null
                or n.status != 'resuelto')
            and  n.colaborador_id = '".$id_user."'
            group by n.source_id
            order by n.status = 'escalado' desc, n.view = 'no-leido' desc, n.created_at desc, n.view asc
            limit ".$limit."");
    }

    public static function getCountProspectosNotifications($id_user){
        return count(ProspectosNotificationsRep::getProspectosNotifications($id_user, $limit=100000000000000000));
    }

    public static function getCountOportunidadesNotifications($id_user){
        return count(ProspectosNotificationsRep::getOportunidadesNotifications($id_user, $limit=100000000000000000));
    }

    public static function getOportunidadesNotifications($id_user, $limit){
        return DB::select("select n.*, p.*, o.*, 
                            CONCAT(u.nombre, ' ', u.apellido) as colaborador, 
                            DATEDIFF(now(),n.created_at)as days,
                            CONCAT(p.nombre, ' ', p.apellido) as prospecto
                                from notifications n
                                left join oportunidades o on o.id_oportunidad = n.source_id
                                left join users u on u.id = n.colaborador_id
                                left join oportunidad_prospecto op on op.id_oportunidad = o.id_oportunidad
                                left join prospectos p on p.id_prospecto = op.id_prospecto
                                where n.id in (select id from notifications n2
                                            where DATEDIFF(NOW(), n2.updated_at) <= 3
                                            and n2.view = 'leido'
                                            and (n2.status != 'resuelto'
                                            	or n2.status is null)
                                            and n2.notification_type = 'oportunidad')
                                or ( n.view = 'no-leido'
                                or n.status = 'escalado')
                                and n.notification_type = 'oportunidad'
                                and (n.status is null
                                    or n.status != 'resuelto')
                                and  n.colaborador_id = '".$id_user."'
                                order by n.status = 'escalado' desc, n.view = 'no-leido' desc, n.created_at desc, n.view asc
                                limit ".$limit."");
    }

    public static function updateStatusNotification($source_id){
        $status = Notification::where('source_id', $source_id)->first();
        $status->view = 'leido';
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

     public static function getProspectosByColaboradorToSendNotifications($user_id, $start_date)
    {
        $prospectos = Prospecto::select('prospectos.id_prospecto',
                                        'prospectos.nombre as nombre_prospecto',
                                        'status_prospecto.updated_at',
                                        'detalle_prospecto.telefono',
                                        'cat_status_prospecto.status',
                                        'users.nombre',
                                        'users.apellido',
                                        'users.email',
                                        'users.id as colaborador_id')
                                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('users','colaborador_prospecto.id_colaborador','users.id')
                                ->join('status_prospecto','colaborador_prospecto.id_prospecto','status_prospecto.id_prospecto')
                                ->join('detalle_prospecto','colaborador_prospecto.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                                ->where('status_prospecto.updated_at', '<=', $start_date)
                                ->where('status_prospecto.id_cat_status_prospecto', 2)
                                ->where('colaborador_prospecto.id_colaborador', $user_id)
                                ->groupBy('prospectos.id_prospecto')
                                ->get()
                                ->toArray();
        
        return $prospectos;
    }

    public static function verifyActivityforProspecto($source_id, $notificaton_updated_at)
    {
        $inactivity_period = 0;

        $status_prospecto = StatusProspecto::select('*')
                                                ->where('id_prospecto', $source_id)
                                                ->first();
        
        if (isset($status_prospecto->updated_at)) {
            $inactivity_period = UtilService::getHoursDifferenceForTimeStamps($status_prospecto->updated_at, $notificaton_updated_at);
        }

        return $inactivity_period;
    }

    public static function updateAttemptsAndInactivityforExisitingProspectoNotification($prospecto_id, $new_inactivity_period, $attempts=NULL, $view=NULL)
    {
        $prospectos  =  Notification::where('source_id', $prospecto_id)
                                    ->where('notification_type', 'prospecto')
                                    ->where(function($q) {
                                        $q->where('status', '!=', 'resuelto')
                                          ->orWhereNull('status');
                                    })
                                    ->get();

        if (count($prospectos) > 0) {
            foreach ($prospectos as $key => $prospecto) {
            
                $prospecto->inactivity_period = $new_inactivity_period;
                if(!is_null($attempts)){
                    $prospecto->attempts = $prospecto->attempts + 1;
                }
                if(!is_null($view)){
                    $prospecto->view = $view;
                }
                $prospecto->save();
            }
        }
        if (!empty($prospecto)) {
            $prospecto->inactivity_period = $new_inactivity_period;
            if(!is_null($attempts)){
                $prospecto->attempts = $prospecto->attempts + 1;
            }
            $prospecto->save();

            return $prospecto;
        }
    }

}

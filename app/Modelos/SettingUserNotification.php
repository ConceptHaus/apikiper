<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class SettingUserNotification extends Model
{
    protected $table = 'setting_user_notifications';

    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'id_configuracion','id_user', 'configuraciones'
     ];
}

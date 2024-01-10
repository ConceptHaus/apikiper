<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class SettingUserNotification extends Model
{
    protected $table = 'settings_user';

    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'id_configuracion','id_user', 'configuraciones'
     ];
}

<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class CallsProstecto extends Model
{
    protected $table = 'calls_prospectos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'id_prospecto',
        'caller_number',
        'caller_name',
        'caller_city',
        'caller_state',
        'caller_zip',
        'play_recording',
        'device_type',
        'device_make',
        'call_status',
        'call_duration',

    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

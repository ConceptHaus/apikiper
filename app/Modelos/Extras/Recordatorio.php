<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class Recordatorio extends Model
{
    protected $table = 'recordatorios';
    protected $primaryKey = 'id_recordatorio';
    protected $fillable = [
        'id_recordatorio',
        'id_prospecto',
        'id_colaborador',
        'id_oportunidad'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');

    }

    public function detalle(){
        return $this->hasOne('App\Modelos\Extras\DetalleRecordatorio','id_recordatorio','id_recordatorio');
    }
}
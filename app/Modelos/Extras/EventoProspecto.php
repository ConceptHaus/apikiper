<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class EventoProspecto extends Model
{
    protected $table = 'eventos_prospecto';
    protected $primaryKey = 'id_evento_prospecto';
    protected $fillable = [
        'id_evento_prospecto',
        'id_prospecto',
        'id_colaborador',
       
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

    public function detalle(){
        return $this->hasOne('App\Modelos\Extras\DetalleEventoProspecto','id_evento_prospecto','id_evento_prospecto');
    }
}
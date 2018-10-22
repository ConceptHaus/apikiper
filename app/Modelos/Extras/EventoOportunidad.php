<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class EventoOportunidad extends Model
{
    protected $table = 'eventos_oportunidad';
    protected $primaryKey = 'id_evento_oportunidad';
    protected $fillable = [
        'id_evento_oportunidad',
        'id_oportunidad',
        'id_colaborador',
       
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

    
    public function detalle(){
        return $this->hasOne('App\Modelos\Extras\DetalleEventoOportunidad','id_evento_oportunidad','id_evento_oportunidad');
    }
}
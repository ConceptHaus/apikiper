<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleEventoOportunidad extends Model
{
    protected $table = 'detalle_evento_oportunidad';
    protected $primaryKey = 'id_detalle_evento';
    protected $fillable = [
        'id_detalle_evento',
        'id_evento_oportunidad',
        'fecha_evento',
        'hora_evento',
        'nota_evento'
    ];

    public function evento(){
        return $this->belongsTo('App\Modelos\Extras\EventoOportunidad','id_evento_oportunidad','id_evento_oportunidad');
    }
}
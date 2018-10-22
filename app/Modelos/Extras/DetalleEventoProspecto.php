<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleEventoProspecto extends Model
{
    protected $table = 'detalle_evento_prospecto';
    protected $primaryKey = 'id_detalle_evento';
    protected $fillable = [
        'id_detalle_evento',
        'id_evento_prospecto',
        'fecha_evento',
        'hora_evento',
        'nota_evento'
    ];

    public function evento(){
        return $this->belongsTo('App\Modelos\Extras\EventoProspecto','id_evento_prospecto','id_evento_prospecto');
    }
}
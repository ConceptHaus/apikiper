<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleEvento extends Model
{
    protected $table = 'detalle_evento';
    protected $primaryKey = 'id_detalle_evento';
    protected $fillable = [
        'id_detalle_evento',
        'id_evento',
        'fecha_evento',
        'hora_evento',
        'nota_evento'
    ];

    public function evento(){
        return $this->belongsTo('App\Evento','id_evento','id_evento');
    }
}
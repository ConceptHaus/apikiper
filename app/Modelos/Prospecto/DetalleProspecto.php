<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class DetalleProspecto extends Model
{
    protected $table ='detalle_prospecto';
    protected $primaryKey = 'id_detalle_prospecto';
    protected $fillable =[
        'id_detalle_prospecto',
        'id_prospecto',
        'puesto',
        'empresa',
        'telefono',
        'celular',
        'whatsapp',
        'nota'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}
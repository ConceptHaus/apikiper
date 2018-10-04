<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class EtiquetasProspecto extends Model
{
    protected $table = 'etiquetas_prospectos';
    protected $primaryKey = 'id_etiquetas_prospecto';
    protected $fillable = [
        'id_etiquetas_prospecto',
        'id_prospecto',
        'id_etiqueta'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function etiqueta(){
        return $this->belongsTo('App\Modelos\Etiquetas\Etiqueta','id_etiqueta','id_etiqueta');
    }
}
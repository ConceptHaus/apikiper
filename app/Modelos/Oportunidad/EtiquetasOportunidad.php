<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class EtiquetasOportunidad extends Model
{
    protected $table = 'etiquetas_oportunidades';
    protected $primaryKey = 'id_etiquetas_oportunidad';
    protected $fillable = [
        'id_etiquetas_oportunidad',
        'id_oportunidad',
        'id_etiqueta'
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function etiqueta(){
        return $this->belongsTo('App\Modelos\Extras\Etiqueta','id_etiqueta','id_etiqueta');
    }

}

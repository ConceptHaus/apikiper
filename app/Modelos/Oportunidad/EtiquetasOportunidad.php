<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtiquetasOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

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

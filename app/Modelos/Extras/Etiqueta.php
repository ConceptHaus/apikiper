<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etiqueta extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;
    protected $table = 'etiquetas';
    protected $primaryKey = 'id_etiqueta';
    protected $fillable = [
        'id_etiqueta',
        'nombre',
        'descripcion'
    ];
     protected $softCascade = ['prospecto','oportunidad'];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\EtiquetasProspecto','id_etiqueta','id_etiqueta')->whereNull("etiquetas.deleted_at");
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\EtiquetasOportunidad','id_etiqueta','id_etiqueta')->whereNull("etiquetas.deleted_at");
    }


}

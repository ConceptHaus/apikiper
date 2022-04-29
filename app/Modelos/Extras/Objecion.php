<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etiqueta extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;
    protected $table = 'cat_objeciones';
    protected $primaryKey = 'id_objecion';
    protected $fillable = [
        'id_objecion',
        'nombre',
        'descripcion'
    ];
     protected $softCascade = ['prospecto','oportunidad'];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\EtiquetasProspecto','id_etiqueta','id_etiqueta');
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\ObjecionOportunidad','id_objecion','id_objecion');
    }

}
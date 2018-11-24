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
     protected $dates = ['deleted_at'];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }


}

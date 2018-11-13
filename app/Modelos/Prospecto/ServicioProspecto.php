<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicioProspecto extends Model
{

    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'servicio_prospecto';
    protected $primaryKey = 'id_servicio_prospecto';
    protected $fillable = [
        'id_servicio_prospecto',
        'id_prospecto',
        'id_servicio_cat'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function servicio(){
        return $this->belongsTo('App\Modelos\Oportunidad\CatServicios','id_servicio_cat','id_servicio_cat');
    }
}

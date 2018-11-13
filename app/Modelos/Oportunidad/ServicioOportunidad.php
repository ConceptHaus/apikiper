<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicioOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'servicio_oportunidad';
    protected $primaryKey = 'id_servicio_oportunidad';
    protected $fillable = [
        'id_servicio_oportunidad',
        'id_oportunidad',
        'id_servicio_cat'
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function servicio(){
        return $this->belongsTo('App\Modelos\Oportunidad\CatServicios','id_servicio_cat','id_servicio_cat');
    }
}

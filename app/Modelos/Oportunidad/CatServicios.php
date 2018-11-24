<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatServicios extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'cat_servicios';
    protected $primaryKey = 'id_servicio_cat';

    protected $fillable = [
        'id_servicio_cat',
        'nombre',
        'descripcion',
    ];
    protected $dates = ['deleted_at'];

    public function servicio(){
        $this->hasMany('App\Modelos\Oportunidad\ServicioOportunidad','id_servicio_cat','id_servicio_cat');
    }
}

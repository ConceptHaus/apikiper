<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class CatServicios extends Model
{
    protected $table = 'cat_servicios';
    protected $primaryKey = 'id_servicio_cat';

    protected $fillable = [
        'id_servicio_cat',
        'nombre',
        'descripcion',
    ];

    public function servicio(){
        $this->hasMany('App\Modelos\Oportunidad\ServicioOportunidad','id_servicio_cat','id_servicio_cat');
    }
}
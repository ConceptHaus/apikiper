<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class CatStatusOportunidad extends Model
{
    protected $table = 'cat_status_oportunidad';
    protected $primaryKey = 'id_cat_status_oportunidad';
    protected $fillable = [
        'id_cat_status_oportunidad',
        'status',
        'descripcion'
    ];

    public function status(){
        $this->hasMany('App\Modelos\Oportunidad\StatusOportunidad','id_cat_status_oportunidad','id_cat_status_oportunidad');
    }

}
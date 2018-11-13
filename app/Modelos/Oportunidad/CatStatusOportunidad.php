<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatStatusOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

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

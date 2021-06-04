<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'status_oportunidad';
    protected $primaryKey = 'id_status_oportunidad';
    protected $fillable = [
        'id_status_oportunidad',
        'id_oportunidad',
        'id_cat_status_oportunidad',
        'updated_at'
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function status(){
        return $this->belongsTo('App\Modelos\Oportunidad\CatStatusOportunidad','id_cat_status_oportunidad','id_cat_status_oportunidad');
    }
}

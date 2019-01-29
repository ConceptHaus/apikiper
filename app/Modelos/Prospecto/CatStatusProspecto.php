<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CatStatusProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'cat_status_prospecto';
    protected $primaryKey = 'id_cat_status_prospecto';
    protected $fillable = [
        'id_cat_status_prospecto',
        'status',
        'descripcion',
        'color'
    ];

    public function status(){
        return $this->hasMany('App\Modelos\Prospecto\StatusProspecto','id_cat_status_prospecto','id_cat_status_prospecto');
    }
}

<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objecion extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'objeciones_oportunidad';
    protected $primaryKey = 'id_objecion_oportunidad';
    protected $fillable = [
        'id_objecion_oportunidad',
        'id_objecion',
        'id_oportunidad'
        
    ];

    protected $softCascade = ['oportunidad'];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\ObjecionesOportunidad','id_objecion','id_objecion');
    }

}

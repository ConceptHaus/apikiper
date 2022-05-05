<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objecion extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'cat_objeciones';
    protected $primaryKey = 'id_objecion';
    protected $fillable = [
        'id_objecion',
        'nombre',
        'descripcion'
        
    ];

    protected $softCascade = ['oportunidad'];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\ObjecionesOportunidad','id_objecion','id_objecion');
    }

}

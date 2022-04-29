<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjecionesOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'objeciones_oportunidades';
    protected $primaryKey = 'id_objecion_oportunidad';
    protected $fillable = [
        'id_objecion_oportunidad',
        'id_objecion',
        'id_oportunidad'
        
    ];

    protected $dates = ['deleted_at'];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    // public function etiqueta(){
    //     return $this->belongsTo('App\Modelos\Extras\Etiqueta','id_etiqueta','id_etiqueta');
    // }

}

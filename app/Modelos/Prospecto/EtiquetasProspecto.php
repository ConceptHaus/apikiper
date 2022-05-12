<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtiquetasProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'etiquetas_prospectos';
    protected $primaryKey = 'id_etiquetas_prospecto';
    protected $fillable = [
        'id_etiquetas_prospecto',
        'id_prospecto',
        'id_etiqueta',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];
    
    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function etiqueta(){
        return $this->belongsTo('App\Modelos\Extras\Etiqueta','id_etiqueta','id_etiqueta');
    }
}

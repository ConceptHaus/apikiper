<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MedioContactoProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'medio_contacto_prospectos';
    protected $primaryKey = 'id_medio_contacto_prospecto';
    protected $fillable = [
        'id_medio_contacto_prospecto',
        'id_mediocontacto_catalogo',
        'id_prospecto',
        'descripcion',
        'fecha',
        'hora'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function medio_contacto(){
        return $this->belongsTo('App\Modelos\Prospecto\CatMedioContacto','id_mediocontacto_catalogo','id_mediocontacto_catalogo');
    }
}

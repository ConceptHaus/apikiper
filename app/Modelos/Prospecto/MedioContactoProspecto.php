<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class MedioContactoProspecto extends Model
{
    protected $table = 'medio_contacto_prospectos';
    protected $primary = 'id_medio_contacto_prospecto';
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
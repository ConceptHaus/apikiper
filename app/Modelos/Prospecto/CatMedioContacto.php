<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class CatMedioContacto extends Model
{
    protected $table = 'mediocontacto_catalogo';
    protected $primaryKey = 'id_mediocontacto_catalogo';
    protected $fillable = [
        'id_mediocontacto_catalogo',
        'nombre',
    ];

    public function medio_contacto(){
        return $this->hasMany('App\Modelos\Prospecto\MedioContactoProspecto','id_mediocontacto_catalogo','id_mediocontacto_catalogo');
    }

}

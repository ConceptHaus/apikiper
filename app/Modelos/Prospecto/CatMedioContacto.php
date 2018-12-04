<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CatMedioContacto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'mediocontacto_catalogo';
    protected $primaryKey = 'id_mediocontacto_catalogo';
    protected $fillable = [
        'id_mediocontacto_catalogo',
        'nombre',
    ];

    public function medio_contacto(){
        return $this->hasMany('App\Modelos\Prospecto\MedioContactoProspecto','id_mediocontacto_catalogo','id_mediocontacto_catalogo');
    }

    public function medio_contacto_oportunidad(){
      return $this->hasMany('App\Modelos\Oportunidad\MedioContactoOportunidad', 'id_mediocontacto_catalogo','id_mediocontacto_catalogo');
    }

}

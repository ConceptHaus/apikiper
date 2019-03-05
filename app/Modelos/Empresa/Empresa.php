<?php

namespace App\Modelos\Empresa;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use UuidModelTrait;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'empresas';
    protected $primaryKey = 'id_empresa';
    protected $fillable = [
        'nombre',
        'cp',
        'calle',
        'colonia',
        'num_ext',
        'num_int',
        'pais',
        'estado',
        'municipio',
        'cuidad',
        'telefono',
        'num_empleados',
        'id_cat_industria',
        'web',
        'rfc',
        'razon_social'
    ];
    protected $softCascade = [
      'prospectos_empresas',
    ];

    public function prospectos_empresas(){
        return $this->hasMany('App\Modelos\Empresa\EmpresaProspecto','id_empresa','id_empresa');
    }

    public function industria(){
        return $this->hasOne('App\Modelos\Extras\CatIndustria','id_cat_industria','id_cat_industria');
    }
}

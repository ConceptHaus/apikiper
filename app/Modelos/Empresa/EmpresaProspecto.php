<?php

namespace App\Modelos\Empresa;

use Illuminate\Database\Eloquent\Model;

//use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaProspecto extends Model
{
    //use UuidModelTrait;
    //use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'prospectos_empresas';
    protected $primaryKey = 'id_prospecto_empresa';
    protected $fillable = [
        'id_prospecto',
        'id_empresa',
        'deleted_at'
    ];
    
    public function prospectos(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function empresas(){
        return $this->belongsTo('App\Modelos\Empresa\Empresa', 'id_empresa', 'id_empresa');
    }


}

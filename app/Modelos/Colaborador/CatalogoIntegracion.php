<?php

namespace App\Modelos\Colaboradores;

use Illuminate\Database\Eloquent\Model;


class CatalogoIntegracion extends Model
{
     //
    public $table = 'cat_integraciones';

    protected $primaryKey = 'id_cat_integracion';
    protected $fillable = [
        'api',
        'api_token',
        'created_at',
        'updated_at'
    ];

    public function integracion_colaborador(){
    return $this->hasOne('App\Modelos\Colaboradores\IntegracionColaborador','id_cat_integracion','id_cat_integracion');
    }
}
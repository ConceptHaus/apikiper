<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;

class IntegracionColaborador extends Model
{
    protected $table = 'integracion_colaborador';

     protected $primaryKey = 'id_integracion_colaborador';

    protected $fillable = [
      'id_colaborador',
      'id_cat_integracion'
    ];

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

    public function integracion(){
        return $this->belongsTo('App\Modelos\Colaborador\CatalogoIntegracion','id_cat_integracion','id_cat_integracion');
    }
}
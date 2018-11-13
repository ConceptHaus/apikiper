<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ColaboradorProspecto extends Model
{

  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;

    protected $table = 'colaborador_prospecto';
    protected $primaryKey = 'id_colaborador_prospecto';
    protected $fillable = [
        'id_colaborador_prospecto',
        'id_colaborador',
        'id_prospecto'
    ];


    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaboradorDetalle(){
        return $this->hasOne('App\Modelos\Colaborador\DetalleColaborador','id_colaborador','id_colaborador');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id_colaborador','id');
    }

}

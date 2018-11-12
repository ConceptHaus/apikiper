<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ArchivosProspectoColaborador extends Model
{

  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;
  
    protected $table = 'archivos_prospecto_colaborador';
    protected $primary = 'id_archivos_prospecto_colaborador';
    protected $fillable = [
        'id_archivos_prospecto_colaborador',
        'id_colaborador',
        'id_prospecto',
        'nombre',
        'desc',
        'url'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
}

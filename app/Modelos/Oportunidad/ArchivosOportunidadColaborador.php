<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ArchivosOportunidadColaborador extends Model
{

    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'archivos_oportunidad_colaborador';
    protected $primaryKey = 'id_archivos_oportunidad_colaborador';
    protected $fillable = [
        'id_archivos_oportunidad_colaborador',
        'id_colaborador',
        'id_oportunidad',
        'nombre',
        'descripcion',
        'url'
    ];

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');

    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad','id_oportunidad','id_oportunidad');

    }
}

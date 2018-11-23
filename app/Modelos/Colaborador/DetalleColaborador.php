<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleColaborador extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'detalle_colaborador';
    protected $primaryKey = 'id_detalle_colaborador';

    protected $fillable = [
      'id_colaborador',
      'puesto',
      'telefono',
      'celular',
      'whatsapp',
      'fecha_nacimiento'
    ];

    protected $hidden = [
      'id_colaborador'
    ];


    public function colaborador(){
      return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
}

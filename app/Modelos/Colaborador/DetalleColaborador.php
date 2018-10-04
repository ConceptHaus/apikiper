<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;

class DetalleColaborador extends Model
{
    protected $table = 'detalle_colaborador';
    protected $primaryKey = 'id_detalle_colaborador';

    protected $fillable = [
      'id_colaborador',
      'puesto',
      'telefono',
      'fecha_nacimiento',
    ];

    protected $hidden = [
      'id_colaborador'
    ];

    public function colaborador(){
      return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
}
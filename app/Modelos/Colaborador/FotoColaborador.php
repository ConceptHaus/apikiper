<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;

class FotoColaborador extends Model
{
    protected $table = 'fotos_colaboradores';

     protected $primaryKey = 'id_foto_colaboradores';

    protected $fillable = [
      'id_colaborador',
      'url_foto',
    ];

    public function colaborador(){
      return $this->belongsTo('App\Modelos\User','id','id_colaborador');

    }
}
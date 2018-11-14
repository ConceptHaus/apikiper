<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\SoftDeletes;


class FotoColaborador extends Model
{
  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;

    protected $table = 'fotos_colaboradores';

     protected $primaryKey = 'id_foto_colaboradores';

    protected $fillable = [
      'id_colaborador',
      'url_foto',
    ];

    public function colaborador(){
      return $this->belongsTo('App\Modelos\User','id','id_colaborador');

    }

    // public function scopeUrl_foto($query){
    //   return $query->where('colaborador','id_colaborador')
    //                ->select('url_foto')->get();
    // }
}

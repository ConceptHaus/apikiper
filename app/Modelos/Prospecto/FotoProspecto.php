<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\SoftDeletes;


class FotoProspecto extends Model
{
  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;

    protected $table = 'fotos_prospectos';

     protected $primaryKey = 'id_foto_prospectos';

    protected $fillable = [
      'id_prospecto',
      'url_foto',
    ];

    public function colaborador(){
      return $this->belongsTo('App\Modelos\Prospecto','id_prospecto','id_prospecto');

    }

    // public function scopeUrl_foto($query){
    //   return $query->where('colaborador','id_colaborador')
    //                ->select('url_foto')->get();
    // }
}

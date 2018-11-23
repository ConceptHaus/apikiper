<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatFuente extends Model
{
  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;
  
  protected $table = 'cat_fuentes';
  protected $primaryKey = 'id_fuente';
  protected $fillable = [
    'id_fuente',
    'nombre',
    'url'
  ];

  public function prospecto(){
      return $this->belongsTo('App\Modelos\Prospecto\Prospecto','fuente','id_fuente');
  }
}
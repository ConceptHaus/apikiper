<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoAfiliacion extends Model
{
  use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
  use SoftDeletes;
  
  protected $table = 'cat_tipo_afiliacion';
  protected $primaryKey = 'id_cat_tipo_afilia';
  protected $fillable = [
    'id_cat_tipo_afilia',
    'nombre',
    'descripcion',
    'status',
    'created_at',
    'updated_at',
    'deleted_at'
  ];

  public function prospecto(){
      return $this->belongsTo('App\Modelos\DetalleProspecto\DetalleProspecto','tipoafiliacion','id_cat_tipo_afilia');
  }
}
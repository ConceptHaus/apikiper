<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedioContactoOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'medio_contacto_oportunidades';
    protected $primaryKey = 'id_medio_contacto_oportunidad';
    protected $fillable = [
      'id_medio_contacto_prospecto',
      'id_mediocontacto_catalogo',
      'descripcion',
      'fecha',
      'hora',
      'lugar'
    ];

    protected $dates = ['deleted_at'];

    public function oportunidad(){
      return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad', 'id_oportunidad', 'id_oportunidad');
    }

    public function medio_contacto(){
      return $this->belongsTo('App\Modelos\Prospecto\CatMedioContacto', 'id_mediocontacto_catalogo', 'id_mediocontacto_catalogo');
    }
}

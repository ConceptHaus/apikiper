<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'detalle_oportunidad';
    protected $primaryKey = 'id_detalle_oportunidad';
    protected $fillable = [
        'id_detalle_oportunidad',
        'id_oportunidad',
        'descripcion',
        'valor',
        'meses',
        'mes_cierre_estimado',
        'anio_cierre_estimado'
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function status(){
      return $this->belongsTo('App\Modelos\Oportunidad\StatusOportunidad','id_oportunidad','id_oportunidad');
    }
}

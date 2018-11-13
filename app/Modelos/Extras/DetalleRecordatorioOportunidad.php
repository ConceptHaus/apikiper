<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleRecordatorioOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'detalle_recordatorio_op';
    protected $primaryKey = 'id_detalle_recordatorio';
    protected $fillable = [
        'id_detalle_recordatorio',
        'id_recordatorio_oportunidad',
        'fecha_recordatorio',
        'hora_recordatorio',
        'nota_recordatorio'
    ];

    public function recordatorio(){
        return $this->belongsTo('App\Modelos\Extras\RecordatorioOportunidad','id_recordatorio_oportunidad','id_recordatorio_oportunidad');
    }
}

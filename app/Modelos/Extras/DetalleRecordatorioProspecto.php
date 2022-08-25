<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleRecordatorioProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'detalle_recordatorio_prospecto';
    protected $primaryKey = 'id_detalle_recordatorio';
    protected $fillable = [
        'id_detalle_recordatorio',
        'id_recordatorio_prospecto',
        'fecha_recordatorio',
        'hora_recordatorio',
        'nota_recordatorio',
        'aquien_enviar'
    ];

    public function recordatorio(){
        return $this->belongsTo('App\Modelos\Extras\RecordatorioProspecto','id_recordatorio_prospecto','id_recordatorio_prospecto');
    }
}

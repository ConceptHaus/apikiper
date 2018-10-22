<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleRecordatorioOportunidad extends Model
{
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
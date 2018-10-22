<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleRecordatorioProspecto extends Model
{
    protected $table = 'detalle_recordatorio_prospecto';
    protected $primaryKey = 'id_detalle_recordatorio';
    protected $fillable = [
        'id_detalle_recordatorio',
        'id_recordatorio_prospecto',
        'fecha_recordatorio',
        'hora_recordatorio',
        'nota_recordatorio'
    ];

    public function recordatorio(){
        return $this->belongsTo('App\Modelos\Extras\RecordatorioProspecto','id_recordatorio_prospecto','id_recordatorio_prospecto');
    }
}
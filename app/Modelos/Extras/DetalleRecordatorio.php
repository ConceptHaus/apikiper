<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class DetalleRecordatorio extends Model
{
    protected $table = 'detalle_recordatorio';
    protected $primaryKey = 'id_detalle_recordatorio';
    protected $fillable = [
        'id_detalle_recordatorio',
        'id_recordatorio',
        'fecha_recordatorio',
        'hora_recordatorio',
        'nota_recordatorio'
    ];

    public function recordatorio(){
        return $this->belongsTo('App\Modelos\Extras\Recordatorio','id_recordatorio','id_recordatorio');
    }
}
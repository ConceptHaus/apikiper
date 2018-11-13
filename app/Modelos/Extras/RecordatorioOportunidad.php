<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;


class RecordatorioOportunidad extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'recordatorios_oportunidad';
    protected $primaryKey = 'id_recordatorio_oportunidad';
    protected $fillable = [
        'id_recordatorio_oportunidad',
        'id_colaborador',
        'id_oportunidad'
    ];



    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');

    }

    public function detalle(){
        return $this->hasOne('App\Modelos\Extras\DetalleRecordatorioOportunidad','id_recordatorio_oportunidad','id_recordatorio_oportunidad');
    }

    // public function scopeAppoinmentsDue($query){
    //     $now = Carbon::now();
    //     $inTenMinutes = Carbon::now()->addMinutes(10);
    //     return $query->join('detalle_recordatorio','detalle_recordatorio.id_recordatorio','recordatorios.id_recordatorio')
    //                 ->where('detalle_recordatorio.fecha_recordatorio','>=',$now)->where('detalle_recordatorio.fecha_recordatorio','<=',$inTenMinutes);
    // }
}

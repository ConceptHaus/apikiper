<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;


class RecordatorioProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'recordatorios_prospecto';
    protected $primaryKey = 'id_recordatorio_prospecto';
    protected $fillable = [
        'id_recordatorio_prospecto',
        'id_prospecto',
        'id_colaborador',
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }


    public function detalle(){
        return $this->hasOne('App\Modelos\Extras\DetalleRecordatorioProspecto','id_recordatorio_prospecto','id_recordatorio_prospecto');
    }

    // public function scopeAppoinmentsDue($query){
    //     $now = Carbon::now()->toDateTimeString();
    //     $inTwentyMinutes = Carbon::now()->addMinutes(20)->toDateTimeString();
    //     return $query->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
    //                    ->join('users')
    //                    ->whereBetween('detalle_recordatorio_prospecto.fecha_recordatorio',[$now, $inTwentyMinutes])->get();
    //                 //->where('detalle_recordatorio_prospecto.fecha_recordatorio','>=',$now)->where('detalle_recordatorio_prospecto.fecha_recordatorio','<=',$inTenMinutes)->get();
    // }
}

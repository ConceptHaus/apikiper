<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

class RecordatorioColaborador extends Model
{
    protected $table = 'recordatorio_colaborador';
    protected $primaryKey = 'id_recordatorio_colaborador';
    protected $fillable = [
        'id_recordatorio_oportunidad',
        'id_colaborador',
        'id_oportunidad'
    ];



    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }

}

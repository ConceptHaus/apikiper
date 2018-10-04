<?php
namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class ProspectoOportunidad extends Model
{
    protected $table = 'oportunidad_prospecto';
    protected $primaryKey ='id_oportunidad_prospecto';
    protected $fillable = [
        'id_oportunidad_prospecto',
        'id_prospecto',
        'id_oportunidad'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }
}
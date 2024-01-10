<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table ='detalle_prospecto';
    protected $primaryKey = 'id_detalle_prospecto';
    protected $fillable =[
        'id_detalle_prospecto',
        'id_prospecto',
        'puesto',
        'empresa',
        'extension',
        'telefono',
        'celular',
        'whatsapp',
        'nota',
        'extension',
        'nombre_campana',
        'desarrollo',
        'ciudad',
        'campo_adicional_1',
        'campo_adicional_2',
        'campo_adicional_3',
        'campo_adicional_4',
        'campo_adicional_5',
        'campo_adicional_6',
        'campo_adicional_7',
        'campo_adicional_8',
        'campo_adicional_9',
        'campo_adicional_10'
        
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

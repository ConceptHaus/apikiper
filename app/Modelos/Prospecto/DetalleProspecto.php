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
        'id_campana',
        'codigo_interno_cliente',
        'razon_social_empres',
        'rfc_empresa',
        'tipo_cliente',
        'giro_empresa',
        'direccion_fiscal_empresa',
        'num_exterior_empresa',
        'num_interior_empresa',
        'municipio_delegacion_empresa',
        'estado_empresa',
        'pais_empres',
        'colonia_empresa',
        'cp_empresa',
        'direcc_entrega',
        'num_exterior_entreg',
        'num_interior_entreg',
        'municipio_delegacion_entrega',
        'estado_entrega',
        'pais_entrega ',
        'colonia_entrega ',
        'cp_entrega ',
        'rfc_entrega ',
        'nombre_contacto' 
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

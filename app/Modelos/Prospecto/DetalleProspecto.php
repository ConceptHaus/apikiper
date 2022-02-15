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
        'telefono',
        'celular',
        'whatsapp',
        'nota',
        'extension',
        'id_campana',
        'codigo_interno_cliente',
        'rfc_empresa',
        'tipo_cliente',
        'giro_empresa',
        'direccion_fiscal_empresa',
        'num_exterior_empresa',
        'num_interior_empresa',
        'municipio_delegacion_empresa',
        'estado_empresa',
        'pais_entrega ',
        'colonia_entrega ',
        'cp_entrega',
        'rfc_entrega',
        'nombre_contacto', 
        'razon_social_empresa',
        'pais_empresa',
        'colonia_empresa',
        'cp_empresa',
        'direcc_entrega',
        'num_exterior_entrega',
        'num_interior_entrega',
        'estado_entrega',
        'municipio_delegacion_entrega'
        
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

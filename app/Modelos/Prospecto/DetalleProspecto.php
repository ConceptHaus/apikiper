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
    protected $fillable =[ 'id_detalle_prospecto',
        'id_prospecto',
        'puesto',
        'empresa',
        'telefono',
        'celular',
        'whatsapp',
        'nota',
        'extension',
        'id_campana',
        'idIntSoc',
        'tipoafiliacion',
        'fechaingreso',
        'razonsocial',
        'rfc',
        'callef',
        'numf',
        'munf',
        'cpf',
        'correoof',
        'nombrec',
        'correocont',
        'correobol',
        'reprelegal',
        'contacprin',
        'cargo',
        'correoempresarial',
        'sector',
        'TamEmp', 
        'rama',
        'acti',
        'giro',
        'fechapago',
        'no_excel',
        'curriculum_ciudadano',
        'calle_comercial',
        'colonia_comercial',
        'municipio_comercial',
        'cp_comercial',
        'facebook',
        'instagram',
        'twitter',
        'linkedink',
        'colaboradores',
        'colaboradores_afiliados',
        'nrp',
        'nombre_contabilidad',
        'correo_contabilidad',
        'nombre_rh',
        'correo_rh',
        'nombre_capacitacion',
        'correo_capacitacion',
        'nombre_relaciones',
        'correo_relaciones',
        'nombre_recepcion',
        'correo_recepcion',
        'nombre_otro',
        'correo_otro',
        'contacto_ocho',
        'correo_ocho',
        'telefono_dos',
        'telefono_tres',
        'inegi',
        'clave_inegi',
        'clasificacion',
        'esr',
        'fecha_esr',
        'impor_export',
        'paises',
        'mision',
        'vision',
        'valores',
        'mes',
        'promotor',
        'periodo',
        'anio_2020',
        'anio_2021',
        'anio_2022',
        'anio_2023',
        'paginaweb',
        'ciudad',
        'num_empleados',
        'mas_anio_operando',
        'genero',
        'estatus_socio'
        
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
    // public function tipoAfi(){
    //     return $this->hasOne('App\Modelos\Prospecto\TipoAfiliacion','id_cat_tipo_afilia','tipoafiliacion');
    // }
}

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
        'correof',
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
        'fechapago'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

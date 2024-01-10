<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CamposAdicionalesProspecto extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table ='campos_adicionales_prospectos';
    protected $primaryKey = 'id_campo_adicional';
    protected $fillable =[
        'id_campo_adicional',
        'nombre_campo',
        'column_table',
        'requerido',
        'status'
        
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }
}

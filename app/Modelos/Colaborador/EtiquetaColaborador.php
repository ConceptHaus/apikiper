<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtiquetaColaborador extends Model
{
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;
    
    protected $table = 'etiqueta_colaboradors';
    protected $primaryKey = 'id_et_col';
    protected $fillable = [
        'id_et_col',
        'id_user',
        'id_etiqueta'
    ];

    protected $dates = ['deleted_at'];

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_user');
    }
    
    public function etiqueta(){
        return $this->belongsTo('App\Modelos\Extras\Etiqueta','id_etiqueta','id_etiqueta');
    }
}

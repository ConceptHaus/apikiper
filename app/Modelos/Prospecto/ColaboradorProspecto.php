<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class ColaboradorProspecto extends Model
{
    protected $table = 'colaborador_prospecto';
    protected $primary = 'id_colaborador_prospecto';
    protected $fillable = [
        'id_colaborador_prospecto',
        'id_colaborador',
        'id_prospecto'
    ];
    
    public function colaborador(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_colaborador','id_colaborador');
    }

    public function colaboradorDetalle(){
        return $this->hasOne('App\Modelos\Colaborador\DetalleColaborador','id_colaborador','id_colaborador');
    }

    public function prospecto(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
    
}
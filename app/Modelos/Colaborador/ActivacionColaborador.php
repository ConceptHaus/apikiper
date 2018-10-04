<?php

namespace App\Modelos\Colaborador;

use Illuminate\Database\Eloquent\Model;

class ActivacionColaborador extends Model
{
    public $table = 'activacion_colaborador';

    protected $primaryKey = 'id_colaborador';
    protected $fillable = [
        'id_colaborador',
        'remember_token',
        
    ];

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
}
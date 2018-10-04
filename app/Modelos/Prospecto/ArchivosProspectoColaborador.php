<?php 

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class ArchivosProspectoColaborador extends Model
{
    protected $table = 'archivos_prospecto_colaborador';
    protected $primary = 'id_archivos_prospecto_colaborador';
    protected $fillable = [
        'id_archivos_prospecto_colaborador',
        'id_colaborador',
        'id_prospecto',
        'nombre',
        'desc',
        'url'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function colaborador(){
        return $this->belongsTo('App\Modelos\User','id','id_colaborador');
    }
}
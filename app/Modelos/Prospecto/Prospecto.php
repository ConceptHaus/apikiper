<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;

class Prospecto extends Model
{
    use UuidModelTrait;

    protected $table = 'prospectos';
    protected $primaryKey = 'id_prospecto';
    protected $primary = 'id_prospecto';
    protected $fillable = [
        'id_prospecto',
        'nombre',
        'apellido',
        'correo',
        'fuente'
    ];
    
    public function detalle_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\DetalleProspecto','id_prospecto','id_prospecto');
    }

    public function status_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\StatusProspecto','id_prospecto','id_prospecto');
    }

    public function colaborador_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\ColaboradorProspecto','id_prospecto','id_prospecto');
    }

    public function servicio_prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\ServicioProspecto','id_prospecto','id_prospecto');
    }

    public function etiquetas_prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\EtiquetasProspecto','id_prospecto','id_prospecto');
    }

    public function medio_contacto(){
        return $this->hasMany('App\Modelos\Prospecto\MedioContactoProspecto','id_prospecto','id_prospecto');
    }

    public function archivos_prospecto_colaborador(){
        return $this->hasMany('App\Modelos\Prospecto\ArchivosProspectoColaborador','id_prospecto','id_prospecto');
    }

    public function evento(){
        return $this->hasMany('App\Evento','id_prospecto','id_prospecto');
    }

    public function recordatorios(){
        return $this->hasMany('App\Modelos\Extras\Recordatorio','id_prospecto','id_prospecto');
    }

    public function oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ProspectoOportunidad','id_prospecto','id_prospecto');
    }
}
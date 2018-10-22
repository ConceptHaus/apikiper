<?php

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;

 class Oportunidad extends Model
 {
    use UuidModelTrait;

    protected $table = 'oportunidades';   
    protected $primaryKey = 'id_oportunidad';
    protected $fillable = [
        'id_oportunidad',
        'nombre_oportunidad'
    ];

    public function status_oportunidad(){
        return $this->hasOne('App\Modelos\Oportunidad\StatusOportunidad','id_oportunidad','id_oportunidad');
    }

    public function detalle_oportunidad(){
        return $this->hasOne('App\Modelos\Oportunidad\DetalleOportunidad','id_oportunidad','id_oportunidad');
    }

    public function evento(){
        return $this->hasMany('App\Evento','id_oportunidad','id_oportunidad');
    }

    public function servicio_oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ServicioOportunidad','id_oportunidad','id_oportunidad');
    }

    public function etiquetas_oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\EtiquetasOportunidad','id_oportunidad','id_oportunidad');
    }

    public function archivos_oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ArchivosOportunidadColaborador','id_oportunidad','id_oportunidad');
    }

    public function colaborador_oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ColaboradorOportunidad','id_oportunidad','id_oportunidad');
    }

    public function recordatorios(){
        return $this->hasMany('App\Modelos\Extras\Recordatorio','id_oportunidad','id_oportunidad');
    }

    public function prospecto(){
        return $this->hasMany('App\Modelos\Oportunidad\ProspectoOportunidad','id_oportunidad','id_oportunidad');
    }

 }

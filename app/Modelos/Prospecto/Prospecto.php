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

    public function eventos(){
        return $this->hasMany('App\Modelos\Extras\EventoProspecto','id_prospecto','id_prospecto');
    }

    public function recordatorios(){
        return $this->hasMany('App\Modelos\Extras\RecordatorioProspecto','id_prospecto','id_prospecto');
    }

    public function oportunidades(){
        return $this->hasMany('App\Modelos\Oportunidad\ProspectoOportunidad','id_prospecto','id_prospecto');
    }

    public function scopeGetAllProspectos($query){
        return $query->with('status_prospecto.status')->get();
    }

    public function scopeGetOneProspecto($query,$id){
        return $query->with('status_prospecto.status')
                ->with('detalle_prospecto')
                ->with('colaborador_prospecto.colaboradorDetalle')
                ->with('servicio_prospecto')
                ->with('medio_contacto')
                ->where('id_prospecto',$id)->first();
    }

    public function scopeGetProspectoOportunidades($query,$id){
        return $query->with('oportunidades.prospecto')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoRecordatorios($query,$id){
        return $query->with('recordatorios.detalle')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoEventos($query,$id){
        return $query->with('eventos.detalle')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoEtiquetas($query,$id){
        return $query->with('etiquetas_prospecto')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoArchivos($query,$id){
        return $query->with('archivos_prospecto_colaborador')->where('id_prospecto',$id)->first();
    }
    
}
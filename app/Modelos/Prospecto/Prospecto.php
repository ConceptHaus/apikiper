<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class Prospecto extends Model
{
    use UuidModelTrait;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'prospectos';
    protected $primaryKey = 'id_prospecto';
    protected $fillable = [
        'id_prospecto',
        'nombre',
        'apellido',
        'correo',
        'fuente'
    ];
    protected $softCascade = [
      'detalle_prospecto',
      'status_prospecto',
      'colaborador_prospecto',
      'servicio_prospecto',
      'etiquetas_prospecto',
      'medio_contacto',
      'archivos_prospecto_colaborador',
      'eventos',
      'recordatorios',
      'oportunidades'
    ];

    public function detalle_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\DetalleProspecto','id_prospecto','id_prospecto');
    }

    public function status_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\StatusProspecto','id_prospecto','id_prospecto');
    }
    public function fuente(){
        return $this->hasOne('App\Modelos\Prospecto\CatFuente','id_fuente','fuente');
    }
    
    public function campaign(){
        return $this->hasOne('App\Modelos\Prospecto\CampaignInfo','id_prospecto','id_prospecto');
    }

    public function colaborador_prospecto(){
        return $this->hasOne('App\Modelos\Prospecto\ColaboradorProspecto','id_prospecto','id_prospecto');
    }

    public function servicio_prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\ServicioProspecto','id_prospecto','id_prospecto');
    }

    public function etiquetas_prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\EtiquetasProspecto','id_prospecto','id_prospecto')->whereNull('deleted_at');
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

    public function foto(){
        return $this->hasMany('App\Modelos\Prospecto\FotoProspecto','id_prospecto','id_prospecto');
    }

    public function calls(){
        return $this->hasOne('App\Modelos\Prospecto\CallsProstecto','id_prospecto','id_prospecto');
    }

    public function prospectos_empresas(){
        return $this->hasMany('App\Modelos\Empresa\EmpresaProspecto','id_prospecto','id_prospecto')->with('empresas');
    }

    public function scopeGetAllProspectos($query){
        return $query->with('status_prospecto.status')
                     ->with('foto')
                     ->with('fuente')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    public function scopeGetOneProspecto($query,$id){
        return $query->with('status_prospecto.status')
                
                ->with('detalle_prospecto')
                ->with('foto')
                ->with('fuente')
                ->with('colaborador_prospecto.colaboradorDetalle')
                ->with('colaborador_prospecto.colaborador')
                ->with('servicio_prospecto')
                ->with('oportunidades.oportunidad.detalle_oportunidad.status.status')
                ->with('oportunidades.oportunidad.archivos_oportunidad')
                ->with('medio_contacto')
                ->with('archivos_prospecto_colaborador')
                ->with('calls')
                ->with('etiquetas_prospecto.etiqueta.prospecto')
                ->with('prospectos_empresas.empresas')
                ->where('id_prospecto',$id)->first();
    }

    public function scopeGetProspectoOportunidades($query,$id){
        return $query->with('oportunidades.prospecto')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoRecordatorios($query,$id){
        return $query->with('recordatorios.detalle')->where('id_prospecto',$id)->first();
    }
    public function scopeGetProspectoEventos($query,$id){
        return $query->with('eventos.detalle')->where('id_prospecto',$id)->get();
    }
    public function scopeGetProspectoEtiquetas($query,$id){
        return $query->with('etiquetas_prospecto.etiqueta.prospecto')->where('id_prospecto',$id)->whereNull('deleted_at')->first();
    }
    public function scopeGetProspectoArchivos($query,$id){
        return $query->with('archivos_prospecto_colaborador')->where('id_prospecto',$id)->first();
    }

}

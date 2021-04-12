<?php

namespace App\Modelos;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\CausesActivity;


use Alsofronie\Uuid\UuidModelTrait;



class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use UuidModelTrait;
    use softDeletes;
    use CausesActivity;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    // protected $dates = ['deleted_at'];

    protected $fillable = [
       'id','nombre', 'email', 'password','apellido','is_admin','status'
    ];

    protected $softCascade = [
      'detalle',
      'foto'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

     protected $dates = ['deleted_at'];

    public function detalle(){
        return $this->hasOne('App\Modelos\Colaborador\DetalleColaborador','id_colaborador','id');
    }

    public function integracion(){
        return $this->hasMany('App\Modelos\Colaborador\IntegracionColaborador','id_colaborador','id');
    }

    public function foto(){
        return $this->hasMany('App\Modelos\Colaborador\FotoColaborador','id_colaborador','id');
    }

    public function oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ColaboradorOportunidad','id_colaborador','id');
    }

    public function archivos_oportunidad(){
        return $this->hasMany('App\Modelos\Oportunidad\ArchivosOportunidadColaborador','id_colaborador','id');

    }

    public function archivos_prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\ArchivosProspectoColaborador','id_colaborador','id');

    }

    public function prospecto(){
        return $this->hasMany('App\Modelos\Prospecto\ColaboradorProspecto','id_colaborador','id');

    }

    public function recordatorio(){
        return $this->hasMany('App\Modelos\Extras\RecordatorioOportunidad','id_colaborador','id');

    }

    public function recordatorioColaborador(){
        return $this->hasMany('App\Modelos\Extras\RecordatorioColaborador', 'id_colaborador', 'id')->wherenull('deleted_at');
    }
    
    public function eventos(){
        return $this->hasMany('App\Modelos\Extras\Evento','id_colaborador','id');

    }

    public function etiquetas_colaborador(){
        return $this->hasMany('App\Modelos\Prospecto\EtiquetasProspecto','id_user','id');
    }

    public function scopeGetAllUsers($query){
        return $query->with('detalle','foto')
                     ->orderBy('created_at','desc')
                     ->get();
    }

    public function scopeGetOneUser($query,$id){
        return $query->where('id',$id)->first();
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->hasOne('App\Modelos\Role', 'id', 'role_id');
    }
}

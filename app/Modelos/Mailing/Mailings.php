<?php

namespace App\Modelos\Mailing;

use Illuminate\Database\Eloquent\Model;
// use Alsofronie\Uuid\UuidModelTrait;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Mailings extends Model
{
    // use UuidModelTrait;
    // use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    // use SoftDeletes;

    protected $table = 'mailings';
    protected $primaryKey = 'id_mailing';
    protected $fillable = [
      'titulo_campaña',
      'list_address'
    ];

    public function detalle (){
      return $this->hasOne('App\Modelos\Mailing\DetalleMailings','id_mailing','id_mailing');
    }

    public function imagenes(){
      return $this->hasMany('App\Modelos\Mailing\ImagesMailings','id_mailing','id_mailing');
    }

    public function scopeGetAll ($query){
      return $query->with('detalle', 'imagenes')->orderBy('created_at', 'DESC')->get();
    }

    public function scopeGetOne ($query, $id){
      return $query->where('id_mailing', $id)
                   ->with('detalle', 'imagenes')
                   ->first();
    }
}

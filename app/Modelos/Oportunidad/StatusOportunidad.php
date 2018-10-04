<?php 

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class StatusOportunidad extends Model
{
    protected $table = 'status_oportunidad';
    protected $primaryKey = 'id_status_oportunidad';
    protected $fillable = [
        'id_status_oportunidad',
        'id_oportunidad',
        'id_cat_status_oportunidad'
    ];

    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }

    public function status(){
        return $this->belongsTo('App\Modelos\Oportunidad\CatStatusOportunidad','id_cat_status_oportunidad','id_cat_status_oportunidad');
    }
}
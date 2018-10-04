<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class StatusProspecto extends Model
{
    protected $table = 'status_prospecto';
    protected $primaryKey = 'id_status_prospecto';
    protected $fillable = [
        'id_status_prospecto',
        'id_cat_status_prospecto',
        'id_prospecto'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function status(){
        return $this->belongsTo('App\Modelos\Prospecto\CatStatusProspecto','id_cat_status_prospecto','id_cat_status_prospecto');
    }
}

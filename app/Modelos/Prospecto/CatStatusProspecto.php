<?php 

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class CatStatusProspecto extends Model
{
    protected $table = 'cat_status_prospecto';
    protected $primaryKey = 'id_cat_status_prospecto';
    protected $fillable = [
        'id_cat_status_prospecto',
        'status',
        'descripcion'
    ];

    public function status(){
        return $this->hasMany('App\Modelos\Prospecto\StatusProspecto','id_cat_status_prospecto','id_cat_status_prospecto');
    }
}

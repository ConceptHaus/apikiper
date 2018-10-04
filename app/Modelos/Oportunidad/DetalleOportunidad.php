<?php 

namespace App\Modelos\Oportunidad;

use Illuminate\Database\Eloquent\Model;

class DetalleOportunidad extends Model
{
    protected $table = 'detalle_oportunidad';
    protected $primaryKey = 'id_detalle_oportunidad';
    protected $fillable = [
        'id_detalle_oportunidad',
        'id_oportunidad',
        'descripcion'
    ];
    
    public function oportunidad(){
        return $this->belongsTo('App\Modelos\Oportunidad\Oportunidad','id_oportunidad','id_oportunidad');
    }
}
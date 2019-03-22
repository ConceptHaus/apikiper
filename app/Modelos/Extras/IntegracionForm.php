<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class IntegracionForm extends Model
{
    use SoftDeletes;
    protected $table = 'integracion_forms';
    protected $primaryKey = 'id_integracion_forms';

    protected $fillable = [
        'id_integracion_forms',
        'token',
        'url_success',
        'url_error',
        'nombre',
        'total',
        'status'
    ];

    public function campaign(){
        return $this->hasOne('App\Modelos\Extras\CampaignInfo','id_integracion_forms','id_integracion_forms');
    }
}
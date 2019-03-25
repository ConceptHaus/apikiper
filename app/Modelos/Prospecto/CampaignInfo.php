<?php

namespace App\Modelos\Prospecto;

use Illuminate\Database\Eloquent\Model;

class CampaignInfo extends Model
{
    protected $table = 'campaign_infos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'id_prospecto',
        'id_forms',
        'utm_term',
        'utm_campaign',
        'utm_source',
        'ad_position'
    ];

    public function prospecto(){
        return $this->belongsTo('App\Modelos\Prospecto\Prospecto','id_prospecto','id_prospecto');
    }

    public function form(){
        return $this->belongsTo('App\Modelos\Extras\IntegracionForm','id_forms','id_integracion_forms');
    }
}

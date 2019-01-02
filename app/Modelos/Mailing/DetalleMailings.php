<?php

namespace App\Modelos\Mailing;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleMailings extends Model
{
    use UuidModelTrait;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'detalle_mailings';
    protected $primaryKey = 'id_detalle';
    protected $fillable = [
        'id_mailing',
        'subject',
        'preview_text',
        'text_body',
        'cta'
    ];

    public function mailing (){
      $this->hasOne('App\Modelos\Mailing\Mailings','id_mailing','id_mailing');
    }
}

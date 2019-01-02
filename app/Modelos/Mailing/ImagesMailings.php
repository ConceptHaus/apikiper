<?php

namespace App\Modelos\Mailing;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagesMailings extends Model
{
    use UuidModelTrait;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'images_mailing';
    protected $primaryKey = 'id_imagen';

    protected $fillable = [
      'id_mailing',
      'is_logo',
      'url'
    ];

    public function mailing (){
      $this->hasOne('App\Modelos\Mailing\Mailings','id_mailing','id_mailing');
    }
}

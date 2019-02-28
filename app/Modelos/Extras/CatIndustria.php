<?php

namespace App\Modelos\Extras;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatIndustria extends Model
{
    use UuidModelTrait;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use SoftDeletes;

    protected $table = 'cat_industrias';
    protected $primaryKey = 'id_cat_industria';
    protected $fillable = [
        'nombre',
    ];
    protected $softCascade = [
      'empresas',
    ];

    public function empresas(){
        return $this->belongsTo('App\Modelos\Empresa\Empresa','id_cat_industria','id_cat_industria');
    }
}

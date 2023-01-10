<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Ramas extends Model
{
    protected $table = 'cat_rama';
    


    public function ramas()
    {
        return $this->belongsToMany('App\Modelos\Ramas');
    }

}

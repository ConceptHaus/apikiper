<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    


    public function users()
    {
        return $this->belongsToMany('App\Modelos\User');
    }

}

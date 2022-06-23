<?php

namespace App\Modelos\Mailing;

use Illuminate\Database\Eloquent\Model;
// use Alsofronie\Uuid\UuidModelTrait;
// use Illuminate\Database\Eloquent\SoftDeletes;

class recuperarpassword extends Model
{
    // use UuidModelTrait;
    // use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    // use SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'nombre',
        'apellido',
        'email',
        'password',
        'delete_at',
        'rol_id'
    ];

}
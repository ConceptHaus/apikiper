<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class PasswordRecovery extends Model{
    use UuidModelTrait;

    protected $table = 'password_recovery';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'veritifationToken'
    ];
}
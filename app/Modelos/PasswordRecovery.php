<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class PasswordRecovery extends Model
{
    protected $table = 'password_recoveries';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'verificationToken'
    ];
}

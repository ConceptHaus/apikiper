<?php

namespace App\Http\Services\Password;

use App\Modelos\PasswordRecovery;
use App\Http\Repositories\Password\PasswordRecoveryRep;

class PasswordRecoveryService
{

    private $rep;

    public function __construct(PasswordRecoveryRep $rep){
        $this->rep = $rep;
    }

    public function save($model){
        return $this->rep->save($model);
    }

    public function delete($model){
        $this->rep->delete($model);
    }

    public function findByUser($user_id){
        return $this->rep->findByUser($user_id);
    }

    public function findByToken($token){
        return $this->rep->findByToken($token);
    }

}

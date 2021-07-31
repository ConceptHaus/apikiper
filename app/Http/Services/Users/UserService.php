<?php

namespace App\Http\Services\Users;

use App\Modelos\User;
use App\Http\Repositories\Users\UsersRep;

class UserService
{

    private $userRep;

    public function __construct(UsersRep $rep){
        $this->userRep = $rep;
    }

    public function getUsersByRoleId($role_id){
        return $this->userRep->getUsersByRoleId($role_id);
    }

    public function findById($user_id){
        return $this->userRep->findById($user_id);
    }

    public function save($user){
        $this->userRep->save($user);
    }

}

<?php

namespace App\Http\Repositories\Users;

use App\Modelos\User;

class UsersRep
{

    public function save($user){
        $user->save();
    }

    public function findById($user_id){
        return User::find($user_id);
    }

    public static function getUsersByRoleId($role_id){
        return User::where('role_id', '=', $role_id)->get()->toArray();
    }

    public function findByEmail($email){
        return User::where('email', '=', $email)->get()->first();
    }

}

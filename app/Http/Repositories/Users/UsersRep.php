<?php

namespace App\Http\Repositories\Users;

use App\Modelos\User;

class UsersRep
{
    public static function getUsersByRoleId($role_id){
        return User::where('role_id', '=', $role_id)->get()->toArray();
    }

    public function findById($user_id){
        return User::find($user_id);
    }

    public function save($user){
        $user->save();
    }

}

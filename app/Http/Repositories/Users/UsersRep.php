<?php

namespace App\Http\Repositories\Users;

use App\Modelos\User;

class UsersRep
{
    public static function getUsersByRoleId($role_id)
    {
        return User::where('role_id', '=', $role_id)->get()->toArray();
    }

}

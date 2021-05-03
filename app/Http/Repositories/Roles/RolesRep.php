<?php

namespace App\Http\Repositories\Roles;

use App\Modelos\Role;

class RolesRep
{
    public static function getRolesByRoleID($role_id){
        return Role::all()->where('id', '<', $role_id)->where('is_visible', '=', 1);
    }

}

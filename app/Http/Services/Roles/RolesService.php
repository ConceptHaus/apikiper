<?php
namespace App\Http\Services\Roles;
use App\Http\Repositories\Roles\RolesRep;

class RolesService
{
    private $rep;

    public function __construct(
        RolesRep $rep
    ){
    $this->rep = $rep;
}

    public static function getRolesByRoleID($role_id){
        //The roles the query will retrieve are the roles that are visible and with an ID less than the provided ID
        $roles = RolesRep::getRolesByRoleID($role_id);
        return $roles;
    }

    public function findById($role_id){
        return $this->rep->findById($role_id);
    }
}

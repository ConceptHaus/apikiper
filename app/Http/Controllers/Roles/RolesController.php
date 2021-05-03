<?php

namespace App\Http\Controllers\Roles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

use App\Http\Services\Roles\RolesService;
use DB;
use Auth;

class RolesController extends Controller
{
    public function getAll(Request $request){
        $role_id = Auth::user()->role_id;
        $roles = RolesService::getRolesByRoleID($role_id);
        return response()->json([
            'error'=>false,
            'data'=>$roles,
        ],200);
        
    }
   
}

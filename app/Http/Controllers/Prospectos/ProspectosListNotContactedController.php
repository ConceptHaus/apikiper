<?php

namespace App\Http\Controllers\Prospectos;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Http\Enums\OldRole;

use App\Http\Services\Auth\AuthService;
use App\Http\Services\Prospectos\ProspectosListNotContactedService;

use App\Http\DTOs\Datatable\DatatableResponseDTO;

use App\Modelos\User;
use \App\Http\Enums\Permissions;

class ProspectosListNotContactedController extends Controller
{
    public function findProspectosNotContacted($status) {
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListNContServ = new ProspectosListNotContactedService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListNContServ->getProspectosNotContactedPageByRol($auth->rol, $status);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListNContServ->getProspectosNotContactedPageForAdmin($status);
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListNContServ->getProspectosNotContactedPageForColaborador($auth->id, $status);
            } else {
                $response = [];
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }
}

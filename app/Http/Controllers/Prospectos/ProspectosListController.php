<?php

namespace App\Http\Controllers\Prospectos;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Enums\OldRole;
use App\Http\Services\Auth\AuthService;
use App\Modelos\User;
use \App\Http\Enums\Permissions;

class ProspectosListController extends Controller
{   
    public function findProspectos(){
        $auth = AuthService::getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $permisos = User::getAuthenticatedUserPermissions();
        try{
            if($auth->rol == OldRole::POLANCO() || $auth->rol == OldRole::NAPOLES()){
                $response = ProspectosListService::getProspectosPageByRol($auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = ProspectosListService::getProspectosPageForAdmin();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response->data = ProspectosListService::getAllProspectosPageByColaborador($auth->id);
            }else{
                $response = [];    
            }

            return response()->json(json_encode($response), 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json(json_encode($response), 500);
        }
    }

}

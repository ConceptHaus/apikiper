<?php

namespace App\Http\Controllers\Prospectos;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Enums\OldRole;
use App\Http\Services\Auth\AuthService;

class ProspectosListController extends Controller
{   
    public function findProspectos(){
        $auth = AuthService::getUserAuthInfo(); 
        $response = new DatatableResponseDTO();

        try{
            if($auth->rol == OldRole::POLANCO() || $auth->rol == OldRole::NAPOLES()){
                $response = ProspectosListService::getProspectosPageByRol($auth->rol);
    
            }else if($auth->is_admin){
                $response = ProspectosListService::getProspectosPageForAdmin();
    
            }else{
                $response->data = ProspectosListService::getAllProspectosPageByColaborador($auth->id);
            }

            return response()->json(json_encode($response), 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json(json_encode($response), 500);
        }
    }

}

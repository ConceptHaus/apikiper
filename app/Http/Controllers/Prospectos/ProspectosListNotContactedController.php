<?php

namespace App\Http\Controllers\Prospectos;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

use App\Http\Services\Auth\AuthService;
use App\Http\Services\Prospectos\ProspectosListNotContactedService;

use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListNotContactedController extends Controller
{
    public function findProspectosNotContacted($status) {
        define("POLANCO", 1);
        define("NAPOLES", 2);
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListNContServ = new ProspectosListNotContactedService();


        try{
            if($auth->rol == POLANCO || $auth->rol == NAPOLES){
                $response = $proListNContServ->getProspectosNotContactedPageByRol($auth->rol, $status);
    
            }else if($auth->is_admin){
                $response->data = $proListNContServ->getProspectosNotContactedPageForAdmin($status);
            }else{
                $response = $proListNContServ->getProspectosNotContactedPageForColaborador($auth->id, $status);
                return response()->json([$response], 200);
                $response->recordsTotal = $response->recordsTotal;
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }
}

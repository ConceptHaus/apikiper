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
use App\Http\Services\Prospectos\ProspectosListService;

use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListController extends Controller
{   
    // public function prueba(){
    //     $response = new DatatableResponseDTO();
    //     $response->error = false;
    //     $response->draw = 0;
    //     $response->recordsTotal = 2;
    //     $response->recordsFiltered = 2;
    //     $response->data = array("prospecto2" => array("id_prospecto" => "0193538c-1d11-366b-90ab-4b4b1b4017d1",
    //                                                 "nombre" => "Luisa",
    //                                                 "apellido" => "Altenwerth",
    //                                                 "correo" => "ladd@sdsnad.com",
    //                                                 "fuente" => array("id_fuente" => 2,
    //                                                             "nombre" => "Google")
    //                                                 ),
    //                                                 array("id_prospecto" => "nfuef748rbjdfb784f",
    //                                                 "nombre" => "Fernanda",
    //                                                 "apellido" => "Altamira",
    //                                                 "correo" => "aaaaa@dddddd.com",
    //                                                 "fuente" => array("id_fuente" => 1,
    //                                                             "nombre" => "Facebook")
    //                                                 ),
    //                                                 array("id_prospecto" => "4r4r4r4r4r4r4r4r",
    //                                                 "nombre" => "Jose",
    //                                                 "apellido" => "Pepe",
    //                                                 "correo" => "rrrrrrrrrr@hhhhhhhhhh.com",
    //                                                 "fuente" => array("id_fuente" => 1,
    //                                                             "nombre" => "Facebook")
    //                                                 )
    //                                 );
    //     return response()->json($response, 200);
    // }
    
    public function findProspectos(){
        define("POLANCO", 1);
        define("NAPOLES", 2);
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        try{
            if($auth->rol == POLANCO || $auth->rol == NAPOLES){
                $response = $proListServ->getProspectosPageByRol($auth->rol);
    
            }else if($auth->is_admin){
                $response->data = $proListServ->getProspectosPageForAdmin();
            }else{
                $response = $proListServ->getAllProspectosPageByColaborador($auth->id);
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

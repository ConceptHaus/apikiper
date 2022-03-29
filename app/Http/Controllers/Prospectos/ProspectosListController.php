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
use App\Http\DTOs\Datatable\PagingInfoDTO;

use App\Modelos\User;
use \App\Http\Enums\Permissions;

class ProspectosListController extends Controller
{   

    public function findProspectos(Request $request){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $paginacion = $this->findPaginacion($request);

        $nombres = json_decode($request->nombres);
        $correos = json_decode($request->correos);
        $telefonos = json_decode($request->telefonos);
        $fechaInicio = json_decode($request->fechaInicio);
        $fechaFin = json_decode($request->fechaFin);
        $nombre_empresa = json_decode($request->empresa);
        $estatus = json_decode($request->estatus);
        $fuente = json_decode($request->fuente);
        $etiqueta = json_decode($request->etiqueta);
        $colaboradores = json_decode($request->colaborador);
        
        $permisos = User::getAuthenticatedUserPermissions();
        
        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getProspectosPageByRol($auth->id, $auth->rol, $paginacion, $nombres, $correos,  $telefonos, $fechaInicio, $fechaFin, $nombre_empresa, $estatus, $fuente, $etiqueta );
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getProspectosPageForAdmin($paginacion, $nombres, $correos, $telefonos, $fechaInicio, $fechaFin, $nombre_empresa, $estatus, $fuente, $etiqueta, $colaboradores);
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getAllProspectosPageByColaborador($auth->id, $paginacion, $nombres, $correos, $telefonos, $fechaInicio, $fechaFin, $nombre_empresa, $estatus, $fuente, $etiqueta );

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findCountProspectos(Request $request){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $paginacion = $this->findPaginacion($request);

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getCountProspectosForAdmin();
    
            }else if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getCountProspectosByRol($auth->id, $auth->rol, $paginacion);
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getCountAllProspectosByColaborador($auth->id, $paginacion);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findCountProspectos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findCountProspectosNotContacted(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getCountProspectosNotContactedByRol($auth->id, $auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getCountProspectosNotContactedByAdmin();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getCountAllProspectosNotContactedByColaborador($auth->id);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findCountProspectosNotContacted',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findPaginacion($request){
        $response = new PagingInfoDTO();

        $response->start = $request->input("start");
        $response->length = $request->input("length");
        $response->search = $request->input("search.value");
        $response->order = $request->input("order.0.dir");
        $response->nColumn = $request->input("order.0.column");

        return $response;
    }

    public function findProspectosFuentes(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getProspectosFuentesdByRol($auth->id, $auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getProspectosFuentesByAdmin();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getProspectosFuentesByColaborador($auth->id);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosFuentes',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosStatus(){
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        try{
            $response = $proListServ->getProspectosStatus();

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosStatus',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosColaborador(){
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        try{
            $response = $proListServ->getProspectosColaboradores();

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosColaborador',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosEtiquetas(){
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        try{
            $response = $proListServ->getProspectosEtiquetas();

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosEtiquetas',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosCorreos(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getProspectosCorreos($auth->id, $auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getProspectosCorreos();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getProspectosCorreos($auth->id);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosCorreos',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosNombres(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getProspectosNombre($auth->id, $auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getProspectosNombre();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getProspectosNombre($auth->id);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosNombres',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }

    public function findProspectosTelefono(){
        $auth = new AuthService();
        $auth = $auth->getUserAuthInfo(); 
        $response = new DatatableResponseDTO();
        $proListServ = new ProspectosListService();

        $permisos = User::getAuthenticatedUserPermissions();

        try{
            if($auth->rol == OldRole::POLANCO || $auth->rol == OldRole::NAPOLES){
                $response = $proListServ->getProspectosTelefono($auth->id, $auth->rol);
    
            }else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $response = $proListServ->getProspectosTelefono();
    
            }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $response = $proListServ->getProspectosTelefono($auth->id);

            }else{
                $response = [];    
            }

            return response()->json($response, 200);

        }catch(Exception $e){
            echo 'ProspectosListController.findProspectosTelefono',  $e->getMessage(); 

            $response->error = 'Ocurrio un error inesperado';
            return response()->json($response, 500);
        }
    }


}

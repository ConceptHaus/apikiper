<?php

namespace App\Http\Services\Prospectos;

use App\Http\Repositories\Prospectos\ProspectosListRep;
use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListService
{    

    public function findAllProspectosByRol($rol){
        define("POLANCO", 1);
        define("NAPOLES", 2);
        $etiqueta = $rol == POLANCO ? POLANCO : NAPOLES;
        $object = new ProspectosListRep;
        return $object->findAllProspectosByEtiqueta($etiqueta);
    }

    public function getProspectosPageByRol($rol/*, page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;
        $response->recordsTotal = $object->getProspectosCountByEtiqueta($rol);
        //$response->data = $object->createPageForProspectosByEtiqueta();
        //$response->recordsFiltered = $response->data->lenth;
        return $response;
    }
    
    public function findAllProspectosForAdmin(){
        $object = new ProspectosListRep;
        return $object->findAllProspectosForAdmin();
    }

    public function getProspectosPageForAdmin(/*page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;
        $response->recordsTotal = $object->getProspectosCountForAdmin();
        $response->data =  $object->createPageForProspectosForAdmin();
        //$response->recordsFiltered = $response->data->lenth;
        return $response;
    }

    public function findAllProspectosByColaborador($id_colaborador){
        $object = new ProspectosListRep;
        return $object->findAllProspectosByColaborador($id_colaborador);
    }

    public function getAllProspectosPageByColaborador($id_colaborador /*, page_params*/){
        $response = new DatatableResponseDTO;
        $object = new ProspectosListRep;
        // $response->recordsTotal = $object->getProspectosCountByColaborador($id_colaborador);
        $response->data["prospectos2"] = $object->createPageForProspectosByColaborador($id_colaborador);
        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = false;
        $response->recordsFiltered = 5;
        return $response;
    }

}

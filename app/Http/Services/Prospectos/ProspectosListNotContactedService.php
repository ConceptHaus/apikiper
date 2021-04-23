<?php

namespace App\Http\Services\Prospectos;

use App\Http\Repositories\Prospectos\ProspectosListNotContactedRep;
use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListNotContactedService
{    
    public function getProspectosNotContactedPageByRol($rol, $status/*, page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $object->catalogo_fuentes();
        $origen = $object->origen();

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForRol($rol, $status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->getProspectosStatus();
        $response->data["colaboradores"] = $object->getColaboradores();
        $response->data["etiquetas"] = $object->getEtiquetas();

        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = false;
        $response->recordsTotal = $response->data->count();
        $response->recordsFiltered = $response->data->count();

        return $response;
    }

    public function getProspectosNotContactedPageForAdmin($status/*page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $object->catalogo_fuentes();
        $origen = $object->origen();

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForAdmin($status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->getProspectosStatus();
        $response->data["colaboradores"] = $object->getColaboradores();
        $response->data["etiquetas"] = $object->getEtiquetas();

        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = "false";
        $response->recordsTotal = $response->data["prospectos"]->count();
        $response->recordsFiltered = $response->data["prospectos"]->count();

        return $response;
    }

    public function getProspectosNotContactedPageForColaborador($id, $status){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $object->catalogo_fuentes();
        $origen = $object->origen();

        $response->message = "Correcto";
        $response->error = false;

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForColaborador($id, $status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->getProspectosStatus();
        $response->data["colaboradores"] = $object->getColaboradores();
        $response->data["etiquetas"] = $object->getEtiquetas();
        
        $response->recordsTotal = $response->data["prospectos"]->count();
        $response->draw = 1;
        $response->recordsFiltered = $response->data["prospectos"]->count();

        return $response;
    }

    

}

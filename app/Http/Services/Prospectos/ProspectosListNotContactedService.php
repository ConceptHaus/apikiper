<?php

namespace App\Http\Services\Prospectos;

use App\Http\Repositories\Prospectos\ProspectosListRep;
// use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListNotContactedService
{    

    public function findAllProspectosByRol($rol){
        define("POLANCO", 1);
        define("NAPOLES", 2);
        $etiqueta = $rol == POLANCO ? POLANCO : NAPOLES;
        $object = new ProspectosListRep;
        return $object->findAllProspectosByEtiqueta($etiqueta);
    }

    public function getProspectosNotContactedPageByRol($rol, $status/*, page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $this->catalogo_fuentes();
        $origen = $this->origen();

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForRol($rol, $status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->prospectos_status();
        $response->data["colaboradores"] = $object->colaboradores();
        $response->data["etiquetas"] = $object->etiquetas();

        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = "false";
        $response->recordsTotal = $response->data->count();
        $response->recordsFiltered = $response->data->count();

        return $response;
    }

    public function getProspectosNotContactedPageForAdmin($status/*page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $this->catalogo_fuentes();
        $origen = $this->origen();

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForAdmin($status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->prospectos_status();
        $response->data["colaboradores"] = $object->colaboradores();
        $response->data["etiquetas"] = $object->etiquetas();

        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = "false";
        $response->recordsTotal = $response->data->count();
        $response->recordsFiltered = $response->data->count();

        return $response;
    }

    public function getProspectosNotContactedPageForColaborador($id, $status){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListNotContactedRep;

        $catalago_fuentes = $this->catalogo_fuentes();
        $origen = $this->origen();

        $response->data["prospectos"] = $object->createPageForProspectosNotContactedForAdmin($id, $status);
        $response->data["prospectos_fuente"] = $object->FuentesChecker($catalago_fuentes, $origen);
        $response->data["prospectos_status"] = $object->prospectos_status();
        $response->data["colaboradores"] = $object->colaboradores();
        $response->data["etiquetas"] = $object->etiquetas();

        $response->draw = 1;
        $response->message = "Correcto";
        $response->error = "false";
        $response->recordsTotal = $response->data->count();
        $response->recordsFiltered = $response->data->count();

        return $response;
    }

    

}

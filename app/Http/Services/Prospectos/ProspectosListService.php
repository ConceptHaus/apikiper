<?php

namespace App\Http\Services\Prospectos;

use App\Http\Repositories\Prospectos\ProspectosListRep;
use App\Http\DTOs\Datatable\DatatableResponseDTO;

class ProspectosListService
{    
    /*----------------------- LISTA DE PROSPECTOS --------------------------*/
    public function getProspectosPageByRol($rol/*, page_params*/){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;

        $response->message = "Correcto";
        $response->error = false;

        $response->data["prospectos"] =  $object->createPageForProspectosForRol($rol);

        return $response;
    }

    public function getProspectosPageForAdmin($paginacion){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->message = "Correcto";
        $response->error = false;

        $datos =  $object->createPageForProspectosForAdmin($paginacion);
        $response->data = $datos->items("data");

        $response->recordsTotal = $datos->total();
        $response->draw = 0;
        $response->recordsFiltered = $response->recordsTotal;

        return $response;
    }

    public function getAllProspectosPageByColaborador($id_colaborador, $paginacion){
        $response = new DatatableResponseDTO;
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->message = "Correcto";
        $response->error = false;

        $datos = $object->createPageForProspectosByColaborador($id_colaborador, $paginacion);
        $response->data = $datos->items("data");
        
        $response->recordsTotal = $datos->total();
        $response->draw = 0;
        $response->recordsFiltered = $response->recordsTotal;
        
        return $response;
    }


    /*-------------------- TOTAL DE PROSPECTOS ---------------------*/
    public function getCountProspectosForAdmin(){
        $object = new ProspectosListRep;

        $response->data["prospectos_total"] = $object->createPageForProspectosForAdmin()->count();

        return $response;
    }

    public function getCountAllProspectosByColaborador($id_colaborador){
        $object = new ProspectosListRep;

        $response->data["prospectos_total"] = $object->createPageForProspectosByColaborador($id_colaborador)->count();

        return $response;
    }

    public function getCountProspectosByRol(){
        $object = new ProspectosListRep;

        $response->data["prospectos_total"] = $object->createPageForProspectosForRol($rol)->count();

        return $response;
    }

    /*---------------- TOTAL DE PROSPECTOS NO CONTACTADOS ---------------------*/
    public function getCountProspectosNotContactedByAdmin(){
        $object = new ProspectosListRep;

        $response->data["prospectos_nocontactados"] = $object->getProspectosNotContactedCountByAdmin();
        return $response;
    }

    public function getCountAllProspectosNotContactedByColaborador($id_colaborador){
        $object = new ProspectosListRep;

        $response->data["prospectos_nocontactados"] = $object->getProspectosCountByColaborador($id_colaborador);

        return $response;
    }

    public function getCountProspectosNotContactedByRol($rol){
        $object = new ProspectosListRep;

        $response->data["prospectos_nocontactados"] = $object->getProspectosNotContactedCountByRol($rol);

        return $response;
    }

    /*--------------- PROSPECTOS FUENTE ----------------*/
    public function getProspectosFuentesByAdmin(){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByAdmin();

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesByColaborador($id_colaborador){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByColaborador($id_colaborador);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesdByRol($rol){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByRol($rol);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosStatus(){
        $object = new ProspectosListRep;

        $response->data["prospectos_status"] = $object->getProspectosStatus();

        return $response;
    }

    public function getProspectosColaboradores(){
        $object = new ProspectosListRep;

        $response->data["colaboradores"] = $object->getColaboradores();

        return $response;
    }

    public function getProspectosEtiquetas(){
        $object = new ProspectosListRep;

        $response->data["etiquetas"] = $object->getEtiquetas();

        return $response;
    }

    public function getPaginacion($request){
        $response = new PagingInfoDTO();

        $response->start = $request->input("start");
        $response->length = $request->input("length");
        $response->search = $request->input("search.value");
        $response->order = $request->input("order.0.dir");
        $response->nColumn = $request->input("order.0.column");

        return $response;
    }


    public function getStart($paginacion){
        if ($paginacion->start == 0) {
            return $paginacion->start = 1;
        } else {
            return $paginacion->start = ($paginacion->start / $paginacion->length) + 1;   
        }
    }

}

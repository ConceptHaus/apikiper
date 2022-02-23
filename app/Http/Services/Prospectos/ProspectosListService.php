<?php

namespace App\Http\Services\Prospectos;

use App\Http\Repositories\Prospectos\ProspectosListRep;
use App\Http\DTOs\Datatable\DatatableResponseDTO;

 class ProspectosListService
{    
    /*----------------------- LISTA DE PROSPECTOS --------------------------*/
    public function getProspectosPageByRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->message = "Correcto";
        $response->error = false;

        $datos = $object->createPageForProspectosForRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);
        $response->data =  $datos->items("data");
        $response->noContacted = $object->createCountForProspectosForRolNotContacted($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus)->count();
        $response->prospectOrigin = ProspectosListService::getProspectosFuentesdByRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);

        $response->recordsTotal = $datos->total();
        $response->draw = 0;
        $response->recordsFiltered = $response->recordsTotal;

        return $response;
    }

    public function getProspectosPageForAdmin($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $response = new DatatableResponseDTO();
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->message = "Correcto";
        $response->error = false;

        $datos =  $object->createPageForProspectosForAdmin($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);
        $response->data = $datos->items("data");

        $response->noContacted = $object->createCountForProspectosForAdminNotContacted($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus)->count();
        $response->prospectOrigin = ProspectosListService::getProspectosFuentesByAdmin($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);
        $response->recordsTotal = $datos->total();
        $response->draw = 0;
        $response->recordsFiltered = $response->recordsTotal;

        return $response;
    }

    public function getAllProspectosPageByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $response = new DatatableResponseDTO;
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->message = "Correcto";
        $response->error = false;

        $datos = $object->createPageForProspectosByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);
        $response->data = $datos->items("data");

        $response->noContacted = $object->createCountForProspectosForColaboradorNotContacted($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus)->count();
        $response->prospectOrigin = ProspectosListService::getProspectosFuentesByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);

        
        $response->recordsTotal = $datos->total();
        $response->draw = 0;
        $response->recordsFiltered = $response->recordsTotal;
        
        return $response;
    }


    /*-------------------- TOTAL DE PROSPECTOS ---------------------*/
    public function getCountProspectosForAdmin($busqueda = null){
        $object = new ProspectosListRep;

        $response->data["prospectos_total"] = $object->getProspectosCountByAdmin($busqueda)->total();

        return $response;
    }

    public function getCountAllProspectosByColaborador($id_colaborador, $paginacion){
        $object = new ProspectosListRep;

        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->data["prospectos_total"] = $object->getProspectosCountByColaborador($id_colaborador);

        return $response;
    }

    public function getCountProspectosByRol($id_colaborador, $rol, $paginacion){
        $object = new ProspectosListRep;
        
        $paginacion->start = ProspectosListService::getStart($paginacion);

        $response->data["prospectos_total"] = $object->countProspectosForRol($id_colaborador, $rol);

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

        $response->data["prospectos_nocontactados"] = $object->getProspectosNotContactedCountByColaborador($id_colaborador);

        return $response;
    }

    public function getCountProspectosNotContactedByRol($id_colaborador, $rol){
        $object = new ProspectosListRep;

        $response->data["prospectos_nocontactados"] = $object->getProspectosNotContactedCountByRol($id_colaborador, $rol);

        return $response;
    }

    /*--------------- PROSPECTOS FUENTE ----------------*/
    public function getProspectosFuentesByAdmin($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByAdmin($paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesdByRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesByAdminMovil(){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByAdminMovil();

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesByColaboradorMovil($id_colaborador){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByColaboradorMovil($id_colaborador);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosFuentesdByRolMovil($id_colaborador, $rol){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByRolMovil($id_colaborador, $rol);

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

    public function getProspectosCorreosdByRol($id_colaborador, $rol){
        $object = new ProspectosListRep;

        $catalogo_fuentes = $object->getCatalogosFuentes();
        $origen = $object->getOrigenByRol($id_colaborador, $rol);

        $response->data["prospectos_fuente"] = $object->fuentesChecker($catalogo_fuentes,$origen);

        return $response;
    }

    public function getProspectosCorreos($id_colaborador=null, $rol=null){
        $object = new ProspectosListRep;

        $response->data["prospectos_correos"] = $object->getCorreos($id_colaborador, $rol);

        return $response;
    }

    public function getProspectosNombre($id_colaborador=null, $rol=null){
        $object = new ProspectosListRep;

        $response->data["prospectos_nombre"] = $object->getNombres($id_colaborador, $rol);

        return $response;
    }

    public function getProspectosTelefono($id_colaborador=null, $rol=null){
        $object = new ProspectosListRep;

        $response->data["prospectos_telefono"] = $object->getTelefono($id_colaborador, $rol);

        return $response;
    }
    public function getProspectosRazonsocial($id_colaborador=null, $rol=null){
        $object = new ProspectosListRep;

        $response->data["prospectos_razonsocial"] = $object->getRazonsocial($id_colaborador, $rol);

        return $response;
    }

}

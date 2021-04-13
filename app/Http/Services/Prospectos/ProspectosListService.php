<?php

namespace App\Http\Services\Prospectos;

class ProspectosListService
{
    const POLANCO = 'polanco';
    const NAPOLES = 'napoles';      

    public function findAllProspectosByRol($rol){
        $etiqueta = $rol == OldRole::POLANCO() ? self::POLANCO : self::NAPOLES;
        return ProspectosListRep::findAllProspectosByEtiqueta($etiqueta);
    }

    public function getProspectosPageByRol($rol/*, page_params*/){
        $response = new DatatableResponseDTO();
        $response->recordsTotal = ProspectosListRep::getProspectosCountByEtiqueta($rol);
        //$response->data = ProspectosListRep::createPageForProspectosByEtiqueta();
        //$response->recordsFiltered = $response->data->lenth;
        return $response;
    }
    
    public function findAllProspectosForAdmin(){
        return ProspectosListRep::findAllProspectosForAdmin();
    }

    public function getProspectosPageForAdmin(/*page_params*/){
        $response = new DatatableResponseDTO();
        $response->recordsTotal = ProspectosListRep::getProspectosCountForAdmin();
        //$response->data = ProspectosListRep::createPageForProspectosForAdmin();
        //$response->recordsFiltered = $response->data->lenth;
        return $response;
    }

    public function findAllProspectosByColaborador($id_colaborador){
        return ProspectosListRep::finAllProspectosByColaborador($id_colaborador);
    }

    public function getAllProspectosPageByColaborador($id_colaborador /*, page_params*/){
        $response = new DatatableResponseDTO();
        //$response->recordsTotal = ProspectosListRep::getProspectosCountByColaborador();
        //$response->data = ProspectosListRep::createPageForProspectosByColaborador();
        //$response->recordsFiltered = $response->data->lenth;
        return $response;
    }

}

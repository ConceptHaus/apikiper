<?php

namespace App\Http\Repositories\Prospectos;

class ProspectosListRep
{
    public function findAllProspectosByEtiqueta($etiqueta){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
            ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
            ->where('etiquetas.nombre','like','%'.$etiqueta.'%')
            ->groupby('prospectos.id_prospecto')
            ->orderBy('prospectos.created_at','desc')
            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
            ->get();
    }

    public function getProspectosCountByEtiqueta($etiqueta){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
            ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
            ->where('etiquetas.nombre','like','%'.$etiqueta.'%')
            ->groupby('prospectos.id_prospecto')
            ->orderBy('prospectos.created_at','desc')
            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
            ->count(); /*?*/
    }

    public function findAllProspectosForAdmin(){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->orderBy('prospectos.created_at','desc')
            ->get();
    }

    public function getProspectosCountForAdmin(){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->orderBy('prospectos.created_at','desc')
            ->count();/*?*/
    }

    public function findAllProspectosByColaborador($id_colaborador){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
            ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
            ->wherenull('colaborador_prospecto.deleted_at')
            ->wherenull('prospectos.deleted_at')
            ->orderBy('prospectos.created_at','desc')
            ->get();
    }

    public function getProspectosCountByColaborador($id_colaborador){
        return Prospecto::with('detalle_prospecto')
            ->with('colaborador_prospecto.colaborador.detalle')
            ->with('fuente')
            ->with('status_prospecto.status')
            ->with('prospectos_empresas')
            ->with('prospectos_empresas.empresas')
            ->with('etiquetas_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
            ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
            ->wherenull('colaborador_prospecto.deleted_at')
            ->wherenull('prospectos.deleted_at')
            ->orderBy('prospectos.created_at','desc')
            ->count();/*?*/
    }
}

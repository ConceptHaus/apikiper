<?php

namespace App\Http\Repositories\Prospectos;

use App\Modelos\Prospecto\Prospecto;
use Illuminate\Support\Facades\DB;

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

    public function createPageForProspectosForAdmin(){
        // return Prospecto::with('detalle_prospecto')
        //                         ->with('colaborador_prospecto.colaborador.detalle')
        //                         ->with('fuente')
        //                         ->wherenull('prospectos.deleted_at')
        //                         ->with('status_prospecto.status')
        //                         ->with('prospectos_empresas')
        //                         ->with('etiquetas_prospecto')
        //                         ->with('prospectos_empresas.empresas')
        //                         ->orderBy('prospectos.created_at','desc')
        //                         ->paginate(5, 0, 0, 2);

        return DB::table('prospectos')
        ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
        ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
        ->join('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
        ->wherenull('prospectos.deleted_at')
        // ->select('prospectos.id_prospecto', 'prospectos.nombre as nombre_prospecto', 'prospectos.apellido', 'prospectos.correo', 'detalle_prospecto.telefono', 'users.nombre', 'prospectos.created_at', 'cat_status_prospecto.status', 'cat_fuentes.id_fuente', 'cat_fuentes.url', 'detalle_prospecto.whatsapp')
        ->get();
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

    public function createPageForProspectosByColaborador($id_colaborador){
        return DB::table('users')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_colaborador', '=', 'users.id')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
            ->join('prospectos', 'prospectos.id_prospecto', '=', 'detalle_prospecto.id_prospecto')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->where('users.id', $id_colaborador)
            ->wherenull('colaborador_prospecto.deleted_at')
            ->wherenull('prospectos.deleted_at')
            ->orderBy('prospectos.created_at','desc')
            ->select('prospectos.id_prospecto', 'prospectos.nombre as nombre_prospecto', 'prospectos.apellido', 'prospectos.correo', 'detalle_prospecto.telefono', 'users.nombre', 'prospectos.created_at', 'cat_status_prospecto.status', 'cat_fuentes.id_fuente', 'cat_fuentes.url', 'detalle_prospecto.whatsapp')
            ->get();


        // return Prospecto::with('detalle_prospecto')
        //     ->with('colaborador_prospecto.colaborador.detalle')
        //     ->with('fuente')
        //     ->with('status_prospecto.status')
        //     ->with('prospectos_empresas')
        //     ->with('prospectos_empresas.empresas')
        //     ->with('etiquetas_prospecto')
        //     ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
        //     ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
        //     ->wherenull('colaborador_prospecto.deleted_at')
        //     ->wherenull('prospectos.deleted_at')
        //     ->orderBy('prospectos.created_at','desc')
        //     ->select('prospectos.id_prospecto', 'prospectos.nombre', 'prospectos.apellido', 'prospectos.correo', 'detalle_prospecto.telefono', 'colaborador')
        //     ->get();
        //     ->paginate(2, ['*'], null, 1);/*?*/
    }

}

<?php

namespace App\Http\Repositories\Prospectos;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\CatStatusProspecto;

use Illuminate\Support\Facades\DB;

class ProspectosListNotContactedRep
{
    public function prospectos_status(){
        $prospectos_status = CatStatusProspecto::get();
        return $prospectos_status;
    }

    public function colaboradores(){
        $colaboradores = User::all();
        return $colaboradores;
    }

    public function etiquetas(){
        $etiquetas = DB::table('etiquetas')->select('*')->get();
        return $etiquetas;
    }
        
    public function catalogo_fuentes(){
        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->wherenull('cat_fuentes.deleted_at')
                            ->select('nombre','url','status')->get();
        return $catalogo_fuentes;
    }

    public function origen(){
        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->wherenull('prospectos.deleted_at')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();
        return $origen;
    }

    public function createPageForProspectosNotContactedForRol($rol, $status){
        if($rol == 1){
            $prospectos = Prospecto::with('detalle_prospecto')
                            ->with('colaborador_prospecto.colaborador.detalle')
                            ->with('fuente')
                            ->with('status_prospecto.status')
                            ->with('prospectos_empresas')
                            ->with('prospectos_empresas.empresas')
                            ->with('etiquetas_prospecto')
                            ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                            ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                            ->where('etiquetas.nombre','like','%polanco%')
                            ->where('status_prospecto.id_cat_status_prospecto',$status)
                            ->groupby('prospectos.id_prospecto')
                            ->orderBy('prospectos.created_at','desc')
                            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                            ->get();
        } else if($rol == 2){
            $prospectos = DB::table('prospectos')   
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->where('etiquetas.nombre','like','%napoles%')
                        ->leftJoin('prospectos_empresas', 'prospectos.id_prospecto', '=', 'prospectos_empresas.id_prospecto')
                        ->leftJoin('empresas', 'prospectos_empresas.id_empresa', '=', 'empresas.id_empresa')
                        //->join('empresas', 'prospectos_empresas.id_empresa', 'empresas.id_empresa')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('detalle_prospecto.deleted_at')
                        ->whereNull('status_prospecto.deleted_at')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono', 'empresas.id_empresa','empresas.nombre as empresa', 'detalle_prospecto.empresa as empresa2','detalle_prospecto.whatsapp','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status', 'cat_status_prospecto.color as color')
                        ->orderBy('status_prospecto.updated_at','desc')
                        ->groupBy('prospectos.id_prospecto')
                        ->get();
        }
        return $prospectos;

    }

    public function createPageForProspectosNotContactedForAdmin($status){
        $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('colaborador_prospecto.colaborador.detalle')
                                ->with('fuente')
                                /*->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->join('users', 'users.id', 'colaborador_prospecto.id_prospecto')
                                ->wherenull('users.deleted_at')
                                ->wherenull('colaborador_prospecto.deleted_at')*/
                                ->wherenull('prospectos.deleted_at')
                                ->with('status_prospecto.status')
                                ->with('prospectos_empresas')
                                ->with('etiquetas_prospecto')
                                ->with('prospectos_empresas.empresas')
                                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto',$status)
                                ->orderBy('prospectos.created_at','desc')
                                //->groupBy('prospectos.id_prospecto')
                                ->get();
    }

    public function createPageForProspectosNotContactedForColaborador($id, $status){
        $prospectos = DB::table('prospectos')
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                        ->where('colaborador_prospecto.id_colaborador',$id)
                        ->leftJoin('prospectos_empresas', 'prospectos.id_prospecto', '=', 'prospectos_empresas.id_prospecto')
                        ->leftJoin('empresas', 'prospectos_empresas.id_empresa', '=', 'empresas.id_empresa')
                        //->join('empresas', 'prospectos_empresas.id_empresa', 'empresas.id_empresa')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('detalle_prospecto.deleted_at')
                        ->whereNull('status_prospecto.deleted_at')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono', 'empresas.id_empresa','empresas.nombre as empresa', 'detalle_prospecto.empresa as empresa2','detalle_prospecto.whatsapp','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status', 'cat_status_prospecto.color as color')
                        ->orderBy('status_prospecto.updated_at','desc')
                        ->groupBy('prospectos.id_prospecto')
                        ->get();
    }












    public function FuentesChecker($catalogo,$consulta){

        if(count($catalogo) > count($consulta)){

            if(count($consulta) == 0){

                foreach($catalogo as $fuente){
                    $fuente->total=0;

                }
                return $catalogo;
            }
            else{
                $collection = collect($consulta);
                for($i = 0; $i<count($catalogo); $i++){
                    $match = false;
                    for($j=0; $j<count($consulta); $j++){

                        if( $catalogo[$i]->nombre == $consulta[$j]->nombre ){
                            $match = true;
                            break;
                        }
                    }

                    if(!$match){
                        $catalogo[$i]->total = 0;
                        $collection->push($catalogo[$i]);
                    }
                }
                return $collection->all();
            }



        }
        return $consulta;

}

}

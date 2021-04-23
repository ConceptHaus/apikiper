<?php

namespace App\Http\Repositories\Prospectos;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\CatStatusProspecto;
use App\Modelos\User;
use Illuminate\Support\Facades\DB;

class ProspectosListRep
{
    public function createPageForProspectosForRol($rol){
        if ($rol == 1) {
            return Prospecto::with('detalle_prospecto')
                        ->with('colaborador_prospecto.colaborador.detalle')
                        ->with('fuente')
                        ->with('status_prospecto.status')
                        ->with('prospectos_empresas')
                        ->with('prospectos_empresas.empresas')
                        ->with('etiquetas_prospecto')
                        ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->where('etiquetas.nombre','like','%polanco%')
                        ->groupby('prospectos.id_prospecto')
                        ->orderBy('prospectos.created_at','desc')
                        ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                        ->get();
        } else if($rol == 2){
            return  Prospecto::with('detalle_prospecto')
                        ->with('colaborador_prospecto.colaborador.detalle')
                        ->with('fuente')
                        ->with('prospectos_empresas')
                        ->with('prospectos_empresas.empresas')
                        ->with('status_prospecto.status')
                        ->with('etiquetas_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->where('etiquetas.nombre','like','%napoles%')
                        ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                        ->orderBy('prospectos.created_at','desc')
                        ->groupby('prospectos.id_prospecto')
                        ->get();
        } else {
            return "";
        }
        
    }

    public function getProspectosNotContactedCountByRol($rol){
        if ($rol == 1) {
            return DB::table('prospectos')
                        ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->wherenull('prospectos.deleted_at')
                        ->where('etiquetas.nombre','like','%polanco%')
                        ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        } else if ($rol == 2) {
            return DB::table('prospectos')
            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
            ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
            ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%napoles%')
            ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        } else {
            return "";
        }
    }

    public function getOrigenByRol($rol){
        if($rol == 1){
            return DB::table('prospectos')
                ->distinct()
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->wherenull('prospectos.deleted_at')
                ->where('etiquetas.nombre','like','%polanco%')
                ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                ->groupBy('cat_fuentes.nombre')
                ->get();
        } else if($rol == 2){
            return DB::table('prospectos')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->wherenull('prospectos.deleted_at')
                ->where('etiquetas.nombre','like','%napoles%')
                ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                ->groupBy('cat_fuentes.nombre')->get();
        } else {
            return "";
        }
    }

    /* --------------- ADMIN ------------------ */

    public function createPageForProspectosForAdmin($paginacion){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);

        return DB::table('prospectos')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->join('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->wherenull('prospectos.deleted_at')
            ->when($search, function ($query) use ($search) {
                return $query->where('prospectos.correo', 'like', '%'.$search.'%')
                                ->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                                ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                                ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                                ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                                ->orWhere('users.nombre', 'like', '%'.$search.'%')
                                ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                                ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                                ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                ;
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'users.nombre', 
                'prospectos.created_at', 
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp'
            )
            ->orderBy($orderBy, $paginacion->order)
            ->paginate($paginacion->length, ['*'], null, $paginacion->start);
    }

    public function getOrigenByAdmin(){
        return DB::table('prospectos')
        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
        ->wherenull('prospectos.deleted_at')
        ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
        ->groupBy('cat_fuentes.nombre')->get();
    }

    public function getProspectosCountByAdmin(){
        return Prospecto::all()->count();
    }

    public function getProspectosNotContactedCountByAdmin(){
        return DB::table('prospectos')
            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
    }

    /*-------------- COLABORADORES --------------------*/

    public function createPageForProspectosByColaborador($id_colaborador, $paginacion){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);

        return DB::table('prospectos')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->join('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->wherenull('prospectos.deleted_at')
            ->where('users.id', '=', $id_colaborador)
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%');
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'users.nombre', 
                'prospectos.created_at', 
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp'
            )
            ->orderBy($orderBy, $paginacion->order)
            ->paginate($paginacion->length, ['*'], null, $paginacion->start);
    }

    public function getProspectosCountByColaborador($id_colaborador){
        return Prospecto::join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                    ->where('colaborador_prospecto.id_colaborador',$id_colaborador)->count();
    }

    public function getProspectosNotContactedCountByColaborador($id_colaborador){
        return DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
                                ->wherenull('prospectos.deleted_at')
                                ->wherenull('prospectos.deleted_at')
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
    }

    public function getOrigenByColaborador($id_colaborador){
        return DB::table('prospectos')
        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
        ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
        ->wherenull('prospectos.deleted_at')
        ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
        ->groupBy('cat_fuentes.nombre')->get();
    }

    public function getProspectosStatus(){
        return CatStatusProspecto::get();
    }

    public function getColaboradores(){
        return User::all();
    }

    public function getEtiquetas(){
        return DB::table('etiquetas')->select('*')->get();
    }

    public function fuentesChecker($catalogo,$consulta){

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

    public function getCatalogosFuentes(){
        return DB::table('cat_fuentes')
        ->wherenull('cat_fuentes.deleted_at')
        ->select('nombre','url','status')->get();
    }

    public function getOrderBy($orderBy){
        if ($orderBy == 0) {
            return $orderBy = "prospectos.nombre";

        } else if ($orderBy == 1) {
            return $orderBy = "prospectos.correo";

        } else if ($orderBy == 2) {
            return $orderBy = "detalle_prospecto.telefono";

        } else if ($orderBy == 3) {
            return $orderBy = "users.nombre";

        } else if ($orderBy == 4) {
            return $orderBy = "prospectos.created_at";

        } else if ($orderBy == 5) {
            return $orderBy = "cat_status_prospecto.status";

        } else if ($orderBy == 6) {
            return $orderBy = "cat_fuentes.nombre";
        }

    }

    

}

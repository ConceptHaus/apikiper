<?php

namespace App\Http\Repositories\Prospectos;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\CatStatusProspecto;
use App\Modelos\User;
use Illuminate\Support\Facades\DB;

class ProspectosListRep
{
    public function createPageForProspectosForRol($id_colaborador, $rol, $paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);

        if ($rol == 1) {
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%polanco%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'detalle_prospecto.razonsocial',
                'users.nombre AS colaborador', 
                DB::raw('date_format(prospectos.created_at, "%d/%m/%Y") AS created_at'),
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp',
                'empresas.nombre AS nombre_empresa'
            )
            ->groupby('prospectos.id_prospecto')
            ->orderBy($orderBy, $paginacion->order)
            ->paginate($paginacion->length, ['*'], null, $paginacion->start);

        } else if($rol == 2){
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%napoles%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono',
                'detalle_prospecto.razonsocial', 
                'users.nombre AS colaborador', 
                DB::raw('date_format(prospectos.created_at, "%d/%m/%Y") AS created_at'), 
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp',
                'empresas.nombre AS nombre_empresa'
            )
            ->groupby('prospectos.id_prospecto')
            ->orderBy($orderBy, $paginacion->order)
            ->paginate($paginacion->length, ['*'], null, $paginacion->start);
        } else {
            return "";
        }
        
    }

    public function createCountForProspectosForRolNotContacted($id_colaborador, $rol, $paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);

        if ($rol == 1) {
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%polanco%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->where('status_prospecto.id_cat_status_prospecto','=',2)
            ->groupby('prospectos.id_prospecto')
            ->get();

        } else if($rol == 2){
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%napoles%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->where('status_prospecto.id_cat_status_prospecto','=',2)
            ->groupby('prospectos.id_prospecto')
            ->get();
        } else {
            return "";
        }
        
    }

    public function countProspectosForRol($id_colaborador, $rol){

        if ($rol == 1) {
            return DB::table('prospectos')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->join('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->join('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%polanco%')
            ->where('users.id', '=', $id_colaborador)
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'users.nombre AS colaborador', 
                'prospectos.created_at', 
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp'
            )
            ->count();

        } else if($rol == 2){
            return DB::table('prospectos')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->join('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->join('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', '=', 'prospectos.id_prospecto')
            ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%napoles%')
            ->where('users.id', '=', $id_colaborador)
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'users.nombre AS colaborador', 
                'prospectos.created_at', 
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp'
            )
            ->count();
        } else {
            return "";
        }
        
    }

    public function getProspectosNotContactedCountByRol($id_colaborador, $rol){
        if ($rol == 1) {
            return DB::table('prospectos')
                        ->distinct()
                        ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
                        ->wherenull('prospectos.deleted_at')
                        ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
                        ->where('etiquetas.nombre','like','%polanco%')
                        ->where('status_prospecto.id_cat_status_prospecto','=',2)
                        ->count();

        } else if ($rol == 2) {
            return DB::table('prospectos')
                        ->distinct()
                        ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
                        ->wherenull('prospectos.deleted_at')
                        ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
                        ->where('etiquetas.nombre','like','%napoles%')
                        ->where('status_prospecto.id_cat_status_prospecto','=',2)
                        ->count();

        } else {
            return "";
        }
    }

    public function getOrigenByRol($id_colaborador, $rol, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $search = $paginacion->search;
        
        if ($rol == 1) {
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%polanco%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->distinct()
                ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
                ->where('etiquetas.nombre','like','%polanco%')
                ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(distinct prospectos.id_prospecto) as total, cat_fuentes.nombre'))
                ->groupBy('cat_fuentes.nombre')
                ->get();

        } else if($rol == 2){
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('etiquetas.nombre','like','%napoles%')
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
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->distinct()
            ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
            ->where('etiquetas.nombre','like','%napoles%')
            ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
            ->groupBy('cat_fuentes.nombre')
            ->get();
        }
    }

    public function getOrigenByRolMovil($id_colaborador, $rol){
        if($rol == 1){
            return DB::table('prospectos')
                ->distinct()
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
                ->wherenull('prospectos.deleted_at')
                ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
                ->where('etiquetas.nombre','like','%polanco%')
                ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                ->groupBy('cat_fuentes.nombre')
                ->get();
        } else if($rol == 2){
            return DB::table('prospectos')
                ->distinct()
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
                ->wherenull('prospectos.deleted_at')
                ->where('colaborador_prospecto.id_colaborador','=', $id_colaborador)
                ->where('etiquetas.nombre','like','%napoles%')
                ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                ->groupBy('cat_fuentes.nombre')
                ->get();
        } else {
            return "";
        }
    }

    /* --------------- ADMIN ------------------ */

    public function createCountForProspectosForAdminNotContacted($paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;

        return DB::table('prospectos')
        ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
        ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
        ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
        ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
        ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
        ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
        ->wherenull('prospectos.deleted_at')
        ->where(function ($query) use ($search) {
            $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                    ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                    ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                    ->orWhere('users.nombre', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                    ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                    ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                    ->orWhere('empresas.nombre', 'like', '%'.$search.'%');
        })
        ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
            $query->when($telefonos,  function ($query) use ($telefonos) {
                $query->where(function ($query) use ($telefonos) {
                    $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                });
            });
            $query->when($estatus,  function ($query) use ($estatus) {
                $query->where(function ($query) use ($estatus) {
                    $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                });
            });
            $query->when($fuente,  function ($query) use ($fuente) {
                $query->where(function ($query) use ($fuente) {
                    $query->whereIn('cat_fuentes.nombre', $fuente);
                });
            });
            $query->when($etiqueta,  function ($query) use ($etiqueta) {
                $query->where(function ($query) use ($etiqueta) {
                    $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                });
            });
            $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                });
            });
        })
        ->groupby('prospectos.id_prospecto')
        ->where('status_prospecto.id_cat_status_prospecto','=',2)
        ->get();
    }

    public function createPageForProspectosForAdmin($paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);
        $array = array();

        return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                        ->orWhere('empresas.nombre', 'like', '%'.$search.'%');
            })
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'detalle_prospecto.razonsocial',
                'users.nombre AS colaborador', 
                DB::raw('date_format(prospectos.created_at, "%d/%m/%Y") AS created_at'),
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp',
                'etiquetas.nombre',
                'empresas.nombre AS nombre_empresa'
            )
            ->orderBy($orderBy, $paginacion->order)
            ->groupby('prospectos.id_prospecto')
            ->paginate($paginacion->length, ['*'], null, $paginacion->start);
    }

    public function getOrigenByAdmin($paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        
        return DB::table('prospectos')
        ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
        ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
        ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
        ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
        ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
        ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
        ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
        ->wherenull('prospectos.deleted_at')
        ->where(function ($query) use ($search) {
            $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                    ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                    ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                    ->orWhere('users.nombre', 'like', '%'.$search.'%')
                    ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                    ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                    ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                    ->orWhere('empresas.nombre', 'like', '%'.$search.'%');
        })
        ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
            $query->when($telefonos,  function ($query) use ($telefonos) {
                $query->where(function ($query) use ($telefonos) {
                    $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                });
            });
            $query->when($estatus,  function ($query) use ($estatus) {
                $query->where(function ($query) use ($estatus) {
                    $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                });
            });
            $query->when($fuente,  function ($query) use ($fuente) {
                $query->where(function ($query) use ($fuente) {
                    $query->whereIn('cat_fuentes.nombre', $fuente);
                });
            });
            $query->when($etiqueta,  function ($query) use ($etiqueta) {
                $query->where(function ($query) use ($etiqueta) {
                    $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                });
            });
            $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                });
            });
        })
        ->groupBy('cat_fuentes.nombre')
        ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(distinct prospectos.id_prospecto) as total, cat_fuentes.nombre'))
        ->get();
    }

    public function getOrigenByAdminMovil(){
        return DB::table('prospectos')
        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
        ->wherenull('prospectos.deleted_at')
        ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
        ->groupBy('cat_fuentes.nombre')->get();
    }

    public function getProspectosCountByAdmin($search = null){
        return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                        ->orWhere('empresas.nombre', 'like', '%'.$search.'%');
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'detalle_prospecto.razonsocial',
                'users.nombre AS colaborador', 
                DB::raw('date_format(prospectos.created_at, "%d/%m/%Y") AS created_at'),
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp',
                'etiquetas.nombre',
                'empresas.nombre AS nombre_empresa'
            )
            ->groupby('prospectos.id_prospecto')
            ->paginate();
    }

    public function getProspectosNotContactedCountByAdmin(){
        return DB::table('prospectos')
            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
    }

    /*-------------- COLABORADORES --------------------*/

    public function createCountForProspectosForColaboradorNotContacted($id_colaborador, $paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);
        
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('users.id', '=', $id_colaborador)
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                        ->orWhere('empresas.nombre', 'like', '%'.$search.'%')
                        ;
            })
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->groupby('prospectos.id_prospecto')
            ->where('status_prospecto.id_cat_status_prospecto','=',2)
            ->get();
    }

    public function createPageForProspectosByColaborador($id_colaborador, $paginacion, $telefonos=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $estatus=null){
        $search = $paginacion->search;
        $orderBy = ProspectosListRep::getOrderBy($paginacion->nColumn);
        
            return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('users.id', '=', $id_colaborador)
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                        ->orWhere('empresas.nombre', 'like', '%'.$search.'%')
                        ;
            })
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->select(
                'prospectos.id_prospecto', 
                DB::raw('CONCAT(prospectos.nombre, " ", prospectos.apellido) AS nombre_prospecto'), 
                'prospectos.correo', 
                'detalle_prospecto.telefono', 
                'detalle_prospecto.razonsocial',
                'users.nombre AS colaborador', 
                DB::raw('date_format(prospectos.created_at, "%d/%m/%Y") AS created_at'),
                'cat_status_prospecto.status', 
                'cat_fuentes.nombre as fuente', 
                'cat_fuentes.url', 
                'detalle_prospecto.whatsapp'
                ,
                'empresas.nombre AS nombre_empresa'
            )
            ->orderBy($orderBy, $paginacion->order)
            ->groupby('prospectos.id_prospecto')
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
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
    }

    public function getOrigenByColaborador($id_colaborador, $paginacion, $telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus){
        $search = $paginacion->search;
        
        return DB::table('prospectos')
            ->leftjoin('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'prospectos.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'prospectos.id_prospecto')
            ->leftjoin('empresas', 'empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
            ->wherenull('prospectos.deleted_at')
            ->where('users.id', '=', $id_colaborador)
            ->where(function ($query) use ($search) {
                $query->orWhere('prospectos.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.apellido', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.correo', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.telefono', 'like', '%'.$search.'%')
                        ->orWhere('detalle_prospecto.razonsocial', 'like', '%'.$search.'%')
                        ->orWhere('users.nombre', 'like', '%'.$search.'%')
                        ->orWhere('prospectos.created_at', 'like', '%'.$search.'%')
                        ->orWhere('cat_status_prospecto.status', 'like', '%'.$search.'%')
                        ->orWhere('cat_fuentes.nombre', 'like', '%'.$search.'%')
                        ->orWhere('empresas.nombre', 'like', '%'.$search.'%')
                        ;
            })
            ->where(function ($query) use ($telefonos, $fuente, $etiqueta, $fechaInicio, $fechaFin, $estatus) {
                $query->when($telefonos,  function ($query) use ($telefonos) {
                    $query->where(function ($query) use ($telefonos) {
                        $query->where('detalle_prospecto.telefono', 'LIKE',  "%$telefonos%");
                    });
                });
                $query->when($estatus,  function ($query) use ($estatus) {
                    $query->where(function ($query) use ($estatus) {
                        $query->where('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                    });
                });
                $query->when($fuente,  function ($query) use ($fuente) {
                    $query->where(function ($query) use ($fuente) {
                        $query->whereIn('cat_fuentes.nombre', $fuente);
                    });
                });
                $query->when($etiqueta,  function ($query) use ($etiqueta) {
                    $query->where(function ($query) use ($etiqueta) {
                        $query->where('etiquetas.nombre', 'LIKE', "%$etiqueta%");
                    });
                });
                $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                    $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                    });
                });
            })
            ->where('colaborador_prospecto.id_colaborador',$id_colaborador)
            ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(distinct prospectos.id_prospecto) as total, cat_fuentes.nombre'))
            ->groupBy('cat_fuentes.nombre')->get();
    }

    public function getOrigenByColaboradorMovil($id_colaborador){
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
            
        } else if ($orderBy == 7) {
            return $orderBy = "empresas.nombre";
        }

    }

    public function getCorreos($id_colaborador=null, $rol=null){
        return DB::table('prospectos')
        ->select('prospectos.correo')
        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->wherenull('prospectos.deleted_at')
        ->wherenull('colaborador_prospecto.deleted_at')

        ->when($id_colaborador, function($query) use ($id_colaborador) {
            return $query->where('colaborador_prospecto.id_colaborador', $id_colaborador);
        })     

        ->when($rol, function($query) use ($rol) {
            return $query->join('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                        ->join('etiquetas', 'etiquetas.id_etiqueta', '=', 'etiquetas_prospectos.id_etiqueta')
            ->where('etiquetas.id_etiqueta', $rol);
        })

        ->get();
    }

    public function getNombres($id_colaborador=null, $rol=null){
        return DB::table('prospectos')
        ->select('prospectos.id_prospecto', 'prospectos.nombre', 'prospectos.apellido')
        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->wherenull('prospectos.deleted_at')
        ->wherenull('colaborador_prospecto.deleted_at')

        ->when($id_colaborador, function($query) use ($id_colaborador) {
            return $query->where('colaborador_prospecto.id_colaborador', $id_colaborador);
        })     

        ->when($rol, function($query) use ($rol) {
            return $query->join('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                        ->join('etiquetas', 'etiquetas.id_etiqueta', '=', 'etiquetas_prospectos.id_etiqueta')
            ->where('etiquetas.id_etiqueta', $rol);
        })

        ->get();
    }

    public function getTelefono($id_colaborador=null, $rol=null){
        return DB::table('prospectos')
        ->select('prospectos.id_prospecto', 'detalle_prospecto.telefono')
        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', '=', 'prospectos.id_prospecto')
        ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
        ->wherenull('prospectos.deleted_at')
        ->wherenull('colaborador_prospecto.deleted_at')

        ->when($id_colaborador, function($query) use ($id_colaborador) {
            return $query->where('colaborador_prospecto.id_colaborador', $id_colaborador);
        })     

        ->when($rol, function($query) use ($rol) {
            return $query->join('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                        ->join('etiquetas', 'etiquetas.id_etiqueta', '=', 'etiquetas_prospectos.id_etiqueta')
            ->where('etiquetas.id_etiqueta', $rol);
        })

        ->get();
    }
    
}

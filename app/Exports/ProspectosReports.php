<?php

namespace App\Exports;

use App\Modelos\User;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\EtiquetaProspecto;
use App\Modelos\Prospecto\Etiqueta;
use App\Modelos\Prospecto\ColaboradorProspecto;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use DB;

/**
* @return \Illuminate\Support\Collection
*/
class ProspectosReports implements WithHeadings,FromCollection{
    
    use Exportable;
    protected $desarrollo;
    protected $id_user;
    
    public function __construct($headings, $desarrollo,$id_user, $correos=null, $nombre=null, $telefono=null, $status=null, $grupo=null, $etiquetas=null, $fechaInicio=null, $fechaFin=null, $colaboradores=null)
    {
        $this->headings = $headings;
        $this->desarrollo = $desarrollo;
        $this->id_user = $id_user;
        $this->correos = $correos;
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->status = $status;
        $this->grupo = $grupo;
        $this->etiquetas = $etiquetas;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->colaboradores = $colaboradores;
    }
    
    public function collection()
    {
            
        return $this->getProspectos($this->desarrollo,$this->id_user,$this->correos,$this->nombre,$this->telefono,$this->status,
                                    $this->grupo,$this->etiquetas,$this->fechaInicio,$this->fechaFin,$this->colaboradores);

        
    }
    public function getProspectos($desarrollo, $id_user, $correos, $nombres, $telefonos=null, $estatus=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $colaboradores=null){
        $user = User::find($id_user);
        if($desarrollo == 'all'){
            return DB::table('colaborador_prospecto')
                ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->rightjoin('prospectos', 'prospectos.id_prospecto', '=', 'detalle_prospecto.id_prospecto')
                ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
                ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
                ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
                ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
                ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('empresas', 'prospectos_empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin, $colaboradores) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->orWhere(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->orWhere(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->orWhere(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->orWhere(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->orWhere(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->orWhere(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($colaboradores,  function ($query) use ($colaboradores) {
                        $query->orWhere(function ($query) use ($colaboradores) {
                            $query->whereIn('colaborador_prospecto.id_colaborador', $colaboradores);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                        });
                    });
                })
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        DB::raw("group_concat(medio_contacto_prospectos.descripcion SEPARATOR '  --  ') as seguimiento"),
                        DB::raw("group_concat(etiquetas.nombre SEPARATOR '  --  ') as etiquetas"),
                        'empresas.nombre AS nombre_empresa'
                        )->get();
                
        } else if($desarrollo == 'user'){
            return DB::table('colaborador_prospecto')
            ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
            ->rightjoin('prospectos', 'prospectos.id_prospecto', '=', 'detalle_prospecto.id_prospecto')
            ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
            ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
            ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
            ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
            ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'colaborador_prospecto.id_prospecto')
            ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
            ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
            ->leftjoin('empresas', 'prospectos_empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
                ->where('users.id','=',$id_user)
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                //->where('medio_contacto_prospectos.id_mediocontacto_catalogo','=',1)
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin, $colaboradores) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->orWhere(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->orWhere(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->orWhere(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->orWhere(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->orWhere(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->orWhere(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($colaboradores,  function ($query) use ($colaboradores) {
                        $query->orWhere(function ($query) use ($colaboradores) {
                            $query->whereIn('colaborador_prospecto.id_colaborador', $colaboradores);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                        });
                    });
                })
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        DB::raw("group_concat(medio_contacto_prospectos.descripcion SEPARATOR '  --  ') as seguimiento"),
                        DB::raw("group_concat(etiquetas.nombre SEPARATOR '  --  ') as etiquetas"),
                        'empresas.nombre AS nombre_empresa'
                        )->get();
        } else {
            return DB::table('colaborador_prospecto')
                ->join('detalle_prospecto', 'detalle_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->rightjoin('prospectos', 'prospectos.id_prospecto', '=', 'detalle_prospecto.id_prospecto')
                ->join('cat_fuentes', 'cat_fuentes.id_fuente', '=', 'prospectos.fuente')
                ->leftjoin('status_prospecto', 'status_prospecto.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', '=', 'status_prospecto.id_cat_status_prospecto')
                ->leftjoin('users', 'users.id', '=', 'colaborador_prospecto.id_colaborador')
                ->leftjoin('etiquetas_prospectos', 'etiquetas_prospectos.id_prospecto', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('etiquetas', 'etiquetas.id_etiqueta', 'etiquetas_prospectos.id_etiqueta')
                ->leftjoin('prospectos_empresas', 'prospectos_empresas.id_prospecto', '=', 'colaborador_prospecto.id_prospecto')
                ->leftjoin('empresas', 'prospectos_empresas.id_empresa', '=', 'prospectos_empresas.id_empresa')
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->where([
                            ['etiquetas.nombre','like','%'.$desarrollo.'%'],
                            
                        ])
                ->whereNull('prospectos.deleted_at')
                ->groupby('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where('users.id', '=', $id_user)
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->orWhere(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->orWhere(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->orWhere(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->orWhere(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->orWhere(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->orWhere(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                        });
                    });
                })
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        DB::raw("group_concat(medio_contacto_prospectos.descripcion SEPARATOR '  --  ') as seguimiento"),
                        DB::raw("group_concat(etiquetas.nombre SEPARATOR '  --  ') as etiquetas"),
                        'empresas.nombre AS nombre_empresa'
                        )->get();
        }
        
    }
    public function headings() : array
    {
        return $this->headings;
    }
}


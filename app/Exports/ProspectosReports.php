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
    
    public function __construct($headings, $desarrollo,$id_user, $correos=null, $nombre=null, $telefono=null, $status=null, $grupo=null, $etiquetas=null, $fechaInicio=null, $fechaFin=null, $colaboradores=null, $busqueda=null)
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
        $this->busqueda = $busqueda;
    }
    
    public function collection()
    {
            
        return $this->getProspectos($this->desarrollo,$this->id_user,$this->correos,$this->nombre,$this->telefono,$this->status,
                                    $this->grupo,$this->etiquetas,$this->fechaInicio,$this->fechaFin,$this->colaboradores, $this->busqueda);

        
    }
    public function getProspectos($desarrollo, $id_user, $correos=null, $nombres=null, $telefonos=null, $estatus=null, $fuente=null, $etiqueta=null, $fechaInicio=null, $fechaFin=null, $colaboradores=null, $busqueda=null){
        $user = User::find($id_user);
        if($desarrollo == 'all'){

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
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where(function ($query) use ($busqueda) {
                    $query->orWhere('prospectos.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.apellido', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.correo', 'like', '%'.$busqueda.'%')
                            ->orWhere('detalle_prospecto.telefono', 'like', '%'.$busqueda.'%')
                            ->orWhere('users.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.created_at', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_status_prospecto.status', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_fuentes.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('empresas.nombre', 'like', '%'.$busqueda.'%')
                            ;
                })
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin, $colaboradores) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->where(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->where(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->where(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->where(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->where(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->where(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($colaboradores,  function ($query) use ($colaboradores) {
                        $query->where(function ($query) use ($colaboradores) {
                            $query->whereIn('colaborador_prospecto.id_colaborador', $colaboradores);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->where(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->whereBetween('prospectos.created_at', [$fechaInicio." 00:00:00", $fechaFin." 23:59:59"]);
                        });
                    });
                })
                ->select(
                        DB::raw('IFNULL(CONCAT(users.nombre," ",users.apellido), "Sin Asignar") as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        DB::raw("group_concat(medio_contacto_prospectos.descripcion SEPARATOR '  --  ') as seguimiento"),
                        DB::raw("group_concat(etiquetas.nombre SEPARATOR '  --  ') as etiquetas"),
                        DB::raw('IFNULL(empresas.nombre, "Sin Asignar") as nombre_empresa'),
                        'detalle_prospecto.idIntSoc',
                        'detalle_prospecto.tipoafiliacion',
                        'detalle_prospecto.fechaingreso',
                        'detalle_prospecto.razonsocial',
                        'detalle_prospecto.rfc',
                        'detalle_prospecto.callef',
                        'detalle_prospecto.numf',
                        'detalle_prospecto.munf',
                        'detalle_prospecto.cpf',
                        'detalle_prospecto.correoof',
                        'detalle_prospecto.nombrec',
                        'detalle_prospecto.correocont',
                        'detalle_prospecto.correobol',
                        'detalle_prospecto.reprelegal',
                        'detalle_prospecto.contacprin',
                        'detalle_prospecto.cargo',
                        'detalle_prospecto.correoempresarial',
                        'detalle_prospecto.sector',
                        'detalle_prospecto.TamEmp',
                        'detalle_prospecto.rama',
                        'detalle_prospecto.acti',
                        'detalle_prospecto.giro',
                        'detalle_prospecto.fechapago',
                        'detalle_prospecto.no_excel',
                        'detalle_prospecto.curriculum_ciudadano',
                        'detalle_prospecto.calle_comercial',
                        'detalle_prospecto.colonia_comercial',
                        'detalle_prospecto.municipio_comercial',
                        'detalle_prospecto.cp_comercial',
                        'detalle_prospecto.facebook',
                        'detalle_prospecto.instagram',
                        'detalle_prospecto.twitter',
                        'detalle_prospecto.linkedink',
                        'detalle_prospecto.paginaweb',
                        'detalle_prospecto.colaboradores',
                        'detalle_prospecto.colaboradores_afiliados',
                        'detalle_prospecto.nrp',
                        'detalle_prospecto.nombre_contabilidad',
                        'detalle_prospecto.correo_contabilidad',
                        'detalle_prospecto.nombre_rh',
                        'detalle_prospecto.correo_rh',
                        'detalle_prospecto.nombre_capacitacion',
                        'detalle_prospecto.correo_capacitacion',
                        'detalle_prospecto.nombre_relaciones',
                        'detalle_prospecto.correo_relaciones',
                        'detalle_prospecto.nombre_recepcion',
                        'detalle_prospecto.correo_recepcion',
                        'detalle_prospecto.nombre_otro',
                        'detalle_prospecto.correo_otro',
                        'detalle_prospecto.contacto_ocho',
                        'detalle_prospecto.correo_ocho',
                        'detalle_prospecto.telefono_dos',
                        'detalle_prospecto.telefono_tres',
                        'detalle_prospecto.inegi',
                        'detalle_prospecto.clave_inegi',
                        'detalle_prospecto.clasificacion',
                        'detalle_prospecto.impor_export',
                        'detalle_prospecto.esr',
                        'detalle_prospecto.fecha_esr',
                        'detalle_prospecto.paises',
                        'detalle_prospecto.mision',
                        'detalle_prospecto.vision',
                        'detalle_prospecto.valores',
                        'detalle_prospecto.mes',
                        'detalle_prospecto.promotor',
                        'detalle_prospecto.periodo',
                        'detalle_prospecto.anio_2020',
                        'detalle_prospecto.anio_2021',
                        'detalle_prospecto.anio_2022',
                        'detalle_prospecto.ciudad',
                        'detalle_prospecto.num_empleados',
                        'detalle_prospecto.mas_anio_operando'
                        )->get();
                
        } else if($desarrollo == 'user'){
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
                ->where('users.id','=',$id_user)
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                //->where('medio_contacto_prospectos.id_mediocontacto_catalogo','=',1)
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where(function ($query) use ($busqueda) {
                    $query->orWhere('prospectos.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.apellido', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.correo', 'like', '%'.$busqueda.'%')
                            ->orWhere('detalle_prospecto.telefono', 'like', '%'.$busqueda.'%')
                            ->orWhere('users.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.created_at', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_status_prospecto.status', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_fuentes.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('empresas.nombre', 'like', '%'.$busqueda.'%')
                            ;
                })
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin, $colaboradores) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->where(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->where(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->where(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->where(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->where(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->where(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($colaboradores,  function ($query) use ($colaboradores) {
                        $query->where(function ($query) use ($colaboradores) {
                            $query->whereIn('colaborador_prospecto.id_colaborador', $colaboradores);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->where(function ($query) use ($fechaInicio, $fechaFin) {
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
                        'empresas.nombre AS nombre_empresa',
                        'detalle_prospecto.idIntSoc',
                        'detalle_prospecto.tipoafiliacion',
                        'detalle_prospecto.fechaingreso',
                        'detalle_prospecto.razonsocial',
                        'detalle_prospecto.rfc',
                        'detalle_prospecto.callef',
                        'detalle_prospecto.numf',
                        'detalle_prospecto.munf',
                        'detalle_prospecto.cpf',
                        'detalle_prospecto.correoof',
                        'detalle_prospecto.nombrec',
                        'detalle_prospecto.correocont',
                        'detalle_prospecto.correobol',
                        'detalle_prospecto.reprelegal',
                        'detalle_prospecto.contacprin',
                        'detalle_prospecto.cargo',
                        'detalle_prospecto.correoempresarial',
                        'detalle_prospecto.sector',
                        'detalle_prospecto.TamEmp',
                        'detalle_prospecto.rama',
                        'detalle_prospecto.acti',
                        'detalle_prospecto.giro',
                        'detalle_prospecto.fechapago',
                        'detalle_prospecto.no_excel',
                        'detalle_prospecto.curriculum_ciudadano',
                        'detalle_prospecto.calle_comercial',
                        'detalle_prospecto.colonia_comercial',
                        'detalle_prospecto.municipio_comercial',
                        'detalle_prospecto.cp_comercial',
                        'detalle_prospecto.facebook',
                        'detalle_prospecto.instagram',
                        'detalle_prospecto.twitter',
                        'detalle_prospecto.linkedink',
                        'detalle_prospecto.paginaweb',
                        'detalle_prospecto.colaboradores',
                        'detalle_prospecto.colaboradores_afiliados',
                        'detalle_prospecto.nrp',
                        'detalle_prospecto.nombre_contabilidad',
                        'detalle_prospecto.correo_contabilidad',
                        'detalle_prospecto.nombre_rh',
                        'detalle_prospecto.correo_rh',
                        'detalle_prospecto.nombre_capacitacion',
                        'detalle_prospecto.correo_capacitacion',
                        'detalle_prospecto.nombre_relaciones',
                        'detalle_prospecto.correo_relaciones',
                        'detalle_prospecto.nombre_recepcion',
                        'detalle_prospecto.correo_recepcion',
                        'detalle_prospecto.nombre_otro',
                        'detalle_prospecto.correo_otro',
                        'detalle_prospecto.contacto_ocho',
                        'detalle_prospecto.correo_ocho',
                        'detalle_prospecto.telefono_dos',
                        'detalle_prospecto.telefono_tres',
                        'detalle_prospecto.inegi',
                        'detalle_prospecto.clave_inegi',
                        'detalle_prospecto.clasificacion',
                        'detalle_prospecto.impor_export',
                        'detalle_prospecto.esr',
                        'detalle_prospecto.fecha_esr',
                        'detalle_prospecto.paises',
                        'detalle_prospecto.mision',
                        'detalle_prospecto.vision',
                        'detalle_prospecto.valores',
                        'detalle_prospecto.mes',
                        'detalle_prospecto.promotor',
                        'detalle_prospecto.periodo',
                        'detalle_prospecto.anio_2020',
                        'detalle_prospecto.anio_2021',
                        'detalle_prospecto.anio_2022',
                        'detalle_prospecto.ciudad',
                        'detalle_prospecto.num_empleados',
                        'detalle_prospecto.mas_anio_operando'
                        )->get();
        } else {
            return  DB::table('prospectos')
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
                ->leftjoin('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->where([
                            ['etiquetas.nombre','like','%'.$desarrollo.'%'],
                            
                        ])
                ->whereNull('prospectos.deleted_at')
                ->groupby('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->where('users.id', '=', $id_user)
                ->where(function ($query) use ($busqueda) {
                    $query->orWhere('prospectos.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.apellido', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.correo', 'like', '%'.$busqueda.'%')
                            ->orWhere('detalle_prospecto.telefono', 'like', '%'.$busqueda.'%')
                            ->orWhere('users.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('prospectos.created_at', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_status_prospecto.status', 'like', '%'.$busqueda.'%')
                            ->orWhere('cat_fuentes.nombre', 'like', '%'.$busqueda.'%')
                            ->orWhere('empresas.nombre', 'like', '%'.$busqueda.'%')
                            ;
                })
                ->where(function ($query) use ($correos, $nombres, $telefonos, $estatus, $fuente, $etiqueta, $fechaInicio, $fechaFin) {
                    $query->when($correos,  function ($query) use ($correos) {
                        $query->where(function ($query) use ($correos) {
                            $query->whereIn('prospectos.correo', $correos);
                        });     
                    });
                    $query->when($nombres,  function ($query) use ($nombres) {
                        $query->where(function ($query) use ($nombres) {
                            $query->whereIn('prospectos.id_prospecto', $nombres);
                        });
                    });
                    $query->when($telefonos,  function ($query) use ($telefonos) {
                        $query->where(function ($query) use ($telefonos) {
                            $query->whereIn('detalle_prospecto.id_prospecto', $telefonos);
                        });
                    });
                    $query->when($estatus,  function ($query) use ($estatus) {
                        $query->where(function ($query) use ($estatus) {
                            $query->whereIn('cat_status_prospecto.id_cat_status_prospecto', $estatus);
                        });
                    });
                    $query->when($fuente,  function ($query) use ($fuente) {
                        $query->where(function ($query) use ($fuente) {
                            $query->whereIn('cat_fuentes.nombre', $fuente);
                        });
                    });
                    $query->when($etiqueta,  function ($query) use ($etiqueta) {
                        $query->where(function ($query) use ($etiqueta) {
                            $query->whereIn('etiquetas.id_etiqueta', $etiqueta);
                        });
                    });
                    $query->when($fechaInicio,  function ($query) use ($fechaInicio, $fechaFin) {
                        $query->where(function ($query) use ($fechaInicio, $fechaFin) {
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
                        'empresas.nombre AS nombre_empresa',
                        'detalle_prospecto.idIntSoc',
                        'detalle_prospecto.tipoafiliacion',
                        'detalle_prospecto.fechaingreso',
                        'detalle_prospecto.razonsocial',
                        'detalle_prospecto.rfc',
                        'detalle_prospecto.callef',
                        'detalle_prospecto.numf',
                        'detalle_prospecto.munf',
                        'detalle_prospecto.cpf',
                        'detalle_prospecto.correoof',
                        'detalle_prospecto.nombrec',
                        'detalle_prospecto.correocont',
                        'detalle_prospecto.correobol',
                        'detalle_prospecto.reprelegal',
                        'detalle_prospecto.contacprin',
                        'detalle_prospecto.cargo',
                        'detalle_prospecto.correoempresarial',
                        'detalle_prospecto.sector',
                        'detalle_prospecto.TamEmp',
                        'detalle_prospecto.rama',
                        'detalle_prospecto.acti',
                        'detalle_prospecto.giro',
                        'detalle_prospecto.fechapago',
                        'detalle_prospecto.no_excel',
                        'detalle_prospecto.curriculum_ciudadano',
                        'detalle_prospecto.calle_comercial',
                        'detalle_prospecto.colonia_comercial',
                        'detalle_prospecto.municipio_comercial',
                        'detalle_prospecto.cp_comercial',
                        'detalle_prospecto.facebook',
                        'detalle_prospecto.instagram',
                        'detalle_prospecto.twitter',
                        'detalle_prospecto.linkedink',
                        'detalle_prospecto.paginaweb',
                        'detalle_prospecto.colaboradores',
                        'detalle_prospecto.colaboradores_afiliados',
                        'detalle_prospecto.nrp',
                        'detalle_prospecto.nombre_contabilidad',
                        'detalle_prospecto.correo_contabilidad',
                        'detalle_prospecto.nombre_rh',
                        'detalle_prospecto.correo_rh',
                        'detalle_prospecto.nombre_capacitacion',
                        'detalle_prospecto.correo_capacitacion',
                        'detalle_prospecto.nombre_relaciones',
                        'detalle_prospecto.correo_relaciones',
                        'detalle_prospecto.nombre_recepcion',
                        'detalle_prospecto.correo_recepcion',
                        'detalle_prospecto.nombre_otro',
                        'detalle_prospecto.correo_otro',
                        'detalle_prospecto.contacto_ocho',
                        'detalle_prospecto.correo_ocho',
                        'detalle_prospecto.telefono_dos',
                        'detalle_prospecto.telefono_tres',
                        'detalle_prospecto.inegi',
                        'detalle_prospecto.clave_inegi',
                        'detalle_prospecto.clasificacion',
                        'detalle_prospecto.impor_export',
                        'detalle_prospecto.esr',
                        'detalle_prospecto.fecha_esr',
                        'detalle_prospecto.paises',
                        'detalle_prospecto.mision',
                        'detalle_prospecto.vision',
                        'detalle_prospecto.valores',
                        'detalle_prospecto.mes',
                        'detalle_prospecto.promotor',
                        'detalle_prospecto.periodo',
                        'detalle_prospecto.anio_2020',
                        'detalle_prospecto.anio_2021',
                        'detalle_prospecto.anio_2022',
                        'detalle_prospecto.ciudad',
                        'detalle_prospecto.num_empleados',
                        'detalle_prospecto.mas_anio_operando'
                        )->get();
        }
        
    }
    public function headings() : array
    {
        return $this->headings;
    }
}


<?php

namespace App\Exports;

use App\User;
use App\Prospecto\Prospecto;
use App\Prospecto\EtiquetaProspecto;
use App\Prospecto\Etiqueta;
use App\Prospecto\ColaboradorProspecto;

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
    
    public function __construct($headings, $desarrollo)
    {
        $this->headings = $headings;
        $this->desarrollo = $desarrollo;
    }
    
    public function collection()
    {
            
        return $this->getProspectos($this->desarrollo);

        
    }
    public function getProspectos($desarrollo){
        if($desarrollo == 'all'){
            return DB::table('prospectos')
                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('users','users.id','colaborador_prospecto.id_colaborador')
                ->orderBy('prospectos.created_at','desc')
                ->select('prospectos.nombre as nombre_prospecto',
                        'prospectos.apellido as apellido_prospecto',
                        'detalle_prospecto.telefono',
                        'prospectos.correo',
                        'cat_fuentes.nombre as fuente',
                        'cat_status_prospecto.status',
                        'detalle_prospecto.nota',
                        'users.email as asignado_a',
                        'prospectos.created_at')->get();
        }
        return DB::table('prospectos')
                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('users','users.id','colaborador_prospecto.id_colaborador')
                ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->where('etiquetas.nombre','like','%'.$desarrollo.'%')
                ->groupby('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->select('prospectos.nombre as nombre_prospecto',
                        'prospectos.apellido as apellido_prospecto',
                        'detalle_prospecto.telefono',
                        'prospectos.correo',
                        'cat_fuentes.nombre as fuente',
                        'cat_status_prospecto.status',
                        'detalle_prospecto.nota',
                        'users.email as asignado_a',
                        'prospectos.created_at')->get();
    }
    public function headings() : array
    {
        return $this->headings;
    }
}


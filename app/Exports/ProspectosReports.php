<?php

namespace App\Exports;

use App\User;
use App\Prospecto\Prospecto;
use App\Prospecto\EtiquetaProspecto;
use App\Prospecto\Etiqueta;
use App\Prospecto\ColaboradorProspecto;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use DB;

/**
* @return \Illuminate\Support\Collection
*/
class ProspectosReports implements WithHeadings,WithMultipleSheets{
    
    use Exportable;

    public function __construct($headings)
    {
        $this->headings = $headings;
    }
    
    public function sheets():array
    {
        $sheets = [];
            
        $prospectos_polanco = DB::table('prospectos')
                                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                                ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                                ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                                ->where('etiquetas.nombre','like','%polanco%')
                                ->groupby('prospectos.id_prospecto')
                                ->orderBy('prospectos.created_at','desc')
                                ->select('prospectos.nombre as nombre_prospecto',
                                         'prospectos.apellido as apellido_prospecto',
                                         'detalle_prospecto.telefono',
                                         'prospectos.correo',
                                         'cat_fuentes.nombre as fuente',
                                         'cat_status_prospecto.status',
                                         'detalle_prospecto.nota',
                                         'prospectos.created_at as fecha_registro')->get();
        $sheets[] = $prospectos_polanco;

        $prospectos_napoles = DB::table('prospectos')
                                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                                ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                                ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                                ->where('etiquetas.nombre','like','%napoles%')
                                ->groupby('prospectos.id_prospecto')
                                ->orderBy('prospectos.created_at','desc')
                                ->select('prospectos.nombre as nombre_prospecto',
                                        'prospectos.apellido as apellido_prospecto',
                                        'detalle_prospecto.telefono',
                                        'prospectos.correo',
                                        'cat_fuentes.nombre as fuente',
                                        'cat_status_prospecto.status',
                                        'detalle_prospecto.nota')->get();
        $sheets[]=$prospectos_napoles;
        
        return $sheets;
    }
    
    public function headings() : array
    {
        return $this->headings;
    }
}


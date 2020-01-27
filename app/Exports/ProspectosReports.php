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
    
    public function __construct($headings, $desarrollo,$id_user)
    {
        $this->headings = $headings;
        $this->desarrollo = $desarrollo;
        $this->id_user = $id_user;
    }
    
    public function collection()
    {
            
        return $this->getProspectos($this->desarrollo,$this->id_user);

        
    }
    public function getProspectos($desarrollo, $id_user){
        $user = User::find($id_user);
        if($desarrollo == 'all'){
            return DB::table('prospectos')
                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('users','users.id','colaborador_prospecto.id_colaborador')
                ->join('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->where('medio_contacto_prospectos.id_mediocontacto_catalogo','=',1)
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        'medio_contacto_prospectos.descripcion as seguimiento'
                        )->get();
                
        }
        else if($desarrollo == 'user'){
            return DB::table('prospectos')
                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('users','users.id','colaborador_prospecto.id_colaborador')
                ->where('users.id','=',$id_user)
                ->join('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->where('medio_contacto_prospectos.id_mediocontacto_catalogo','=',1)
                ->whereNull('prospectos.deleted_at')
                ->groupBy('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        'medio_contacto_prospectos.descripcion as seguimiento'
                        )->get();
        }
        return DB::table('prospectos')
                ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('cat_status_prospecto','status_prospecto.id_cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->join('users','users.id','colaborador_prospecto.id_colaborador')
                ->join('medio_contacto_prospectos','prospectos.id_prospecto','medio_contacto_prospectos.id_prospecto')
                ->where('medio_contacto_prospectos.id_mediocontacto_catalogo','=',1)
                ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                ->where([
                            ['etiquetas.nombre','like','%'.$desarrollo.'%'],
                            
                        ])
                ->whereNull('prospectos.deleted_at')
                ->groupby('prospectos.id_prospecto')
                ->orderBy('prospectos.created_at','desc')
                ->select(
                        DB::raw('CONCAT(users.nombre," ",users.apellido) as asesor'),
                        'prospectos.created_at as fecha',
                        'cat_status_prospecto.status as estado',
                        'cat_fuentes.nombre as como se enteró',
                        DB::raw('CONCAT(prospectos.nombre," ",prospectos.apellido) as cliente'),
                        'detalle_prospecto.telefono',
                        'prospectos.correo as mail',
                        'detalle_prospecto.nota as comentarios',
                        'medio_contacto_prospectos.descripcion as seguimiento'
                        )->get();
    }
    public function headings() : array
    {
        return $this->headings;
    }
}


<?php

namespace App\Imports;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Prospecto\StatusProspecto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProspectosImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if($row['nombre'] == null){
            $row['nombre'] = 'n/a';
        }
        if($row['apellido'] == null){
            $row['apellido'] = 'n/a';
        }
        if($row['correo'] == null){
            $row['correo'] = 'n/a';
        }
        if($row['status'] == null){
            $row['status'] = 1;
        }
        $prospecto =  new Prospecto([
            'nombre'=> $row['nombre'],
            'apellido'=>$row['apellido'],
            'correo'=>$row['correo'],
            'fuente'=>3
        ]);
        $prospecto->save();

        $detalle = new DetalleProspecto([
            'puesto'=>$row['puesto'],
            'telefono'=>$row['telefono'],
            'empresa'=>$row['empresa'],
            'celular'=>$row['celular'],
            'nota'=>$row['nota'],
        ]);
        
        $status = new StatusProspecto([
            'id_cat_status_prospecto'=>$row['status']
        ]);
            
            $prospecto->status_prospecto()->save($status);
            $prospecto->detalle_prospecto()->save($detalle);
        
       return  $prospecto;
            
    }
}

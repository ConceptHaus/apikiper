<?php

namespace App\Imports;

use App\Modelos\User;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\ColaboradorProspecto;

use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\DetalleOportunidad;
use App\Modelos\Oportunidad\EtiquetasOportunidad;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ServicioOportunidad;
use App\Modelos\Oportunidad\ProspectoOportunidad;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Modelos\Oportunidad\CatServicios;


use App\Modelos\Extras\Etiqueta;

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
        
        $prospecto =  new Prospecto([
            'nombre'=> (isset($row['nombre']) ? $row['nombre'] : 'n/a'),
            'apellido'=> (isset($row['apellido']) ? $row['nombre'] : ''),
            'correo'=>(isset($row['correo']) ? $row['correo'] : 'n/a'),
            'fuente'=>$row['fuente'] ?: 3
        ]);
        $prospecto->save();

        if(isset($row['colaborador'])){

            $colaborador = User::where('email',$row['colaborador'])->first();
            
            if($colaborador){
                $colaborador_op = new ColaboradorProspecto();
                $colaborador_op->id_colaborador = $colaborador->id;
                $colaborador_op->id_prospecto = $prospecto->id_prospecto;
                $colaborador_op->save();
                
            }    
        }
        if(isset($row['nombre_oportunidad'])){
            $oportunidad = new Oportunidad([
                'nombre_oportunidad'=> $row['nombre_oportunidad']
            ]);
            $oportunidad->save();
            
            $pros_op =  new ProspectoOportunidad([
            'id_prospecto'=>$prospecto->id_prospecto
            ]);
            $oportunidad->prospecto()->save($pros_op);

            $status_op = new StatusOportunidad([
                'id_cat_status_oportunidad'=>1
            ]);
            $oportunidad->status_oportunidad()->save($status_op);
        }


        if(isset($row['valor_oportunidad'])){
            $detalle_op = new DetalleOportunidad([
                'valor' => $row['valor_oportunidad']
            ]);
            $oportunidad->detalle_oportunidad()->save($detalle_op);
        }
        if(isset($row['etiqueta_oportunidad'])){

            $etiqueta = Etiqueta::where('nombre',$row['etiqueta_oportunidad'])->first();
            
            if(!$etiqueta){

                $etiqueta = new Etiqueta;
                $etiqueta->nombre = $row['etiqueta_oportunidad'];
                $etiqueta->save();
            }

            $etiqueta_op = new EtiquetasOportunidad([
                'id_etiqueta'=> $etiqueta->id_etiqueta,
            ]);
            $oportunidad->etiquetas_oportunidad()->save($etiqueta_op);
            
            
        }
        if(isset($row['servicio_oportunidad'])){
            $servicio = CatServicios::where('nombre',$row['servicio_oportunidad'])->first();
            
            if(!$servicio){
                $servicio = new CatServicios;
                $servicio->nombre = $row['servicio_oportunidad'];
                $servicio->save();
            
            }
              
            $servicio_op = new ServicioOportunidad([
                'id_servicio_cat'=> $servicio->id_servicio_cat
            ]);
            $oportunidad->servicio_oportunidad()->save($servicio_op);
            
            
        }

        


        $detalle = new DetalleProspecto([
            'puesto'=>(isset($row['puesto']) ? $row['puesto'] : 'n/a'),
            'telefono'=>(isset($row['telefono']) ? $row['telefono'] : 'n/a'),
            'empresa'=>(isset($row['empresa']) ? $row['empresa'] : 'n/a'),
            'celular'=>(isset($row['celular']) ? $row['celular'] : $row['telefono']),
            'nota'=>(isset($row['nota']) ? $row['nota'] : ''),
        ]);
        
        $status = new StatusProspecto([
            'id_cat_status_prospecto'=>(isset($row['status']) ? $row['status'] : 2)
        ]);
            
            $prospecto->status_prospecto()->save($status);
            $prospecto->detalle_prospecto()->save($detalle);
        
       return  $prospecto;
            
    }
}

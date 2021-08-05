<?php

namespace App\Http\Repositories\Recordatorios;

use App\Modelos\Recordatorios\RecordatoriosOportunidades;
use App\Modelos\Recordatorios\RecordatoriosProspectos;
use App\Modelos\Recordatorios\RecordatoriosUsuarios;

class RecordatoriosRep
{
    public static function getRecordatoriosOportunidades()
    {
        $now            = date('Y-m-d H:i:s');
        $recordatorios  = RecordatoriosOportunidades::join('detalle_recordatorio_op', 'detalle_recordatorio_op.id_recordatorio_oportunidad', 'recordatorios_oportunidad.id_recordatorio_oportunidad')
                                                    ->join('oportunidades', 'oportunidades.id_oportunidad', 'recordatorios_oportunidad.id_oportunidad')
                                                    ->join('users', 'users.id', 'recordatorios_oportunidad.id_colaborador')
                                                    ->join('users_one_signal', 'users_one_signal.user_id', 'users.id')  
                                                    ->where('recordatorios_oportunidad.status', 0)
                                                    ->where('fecha_recordatorio', '<=', $now)
                                                    ->groupBy('recordatorios_oportunidad.id_recordatorio_oportunidad')
                                                    ->get(); 
        
        return $recordatorios;
    }

    public static function getRecordatoriosProspectos()
    {
        $now           = date('Y-m-d H:i:s');
        $recordatorios = RecordatoriosProspectos::join('detalle_recordatorio_prospecto', 'detalle_recordatorio_prospecto.id_recordatorio_prospecto', 'recordatorios_prospecto.id_recordatorio_prospecto')
                                                ->join('users', 'users.id', 'recordatorios_prospecto.id_colaborador')
                                                ->join('prospectos', 'prospectos.id_prospecto', 'recordatorios_prospecto.id_prospecto')
                                                ->join('users_one_signal', 'users_one_signal.user_id', 'users.id')                                       
                                                ->where('recordatorios_prospecto.status', 0)
                                                ->where('fecha_recordatorio', '<=', $now)
                                                ->groupBy('recordatorios_prospecto.id_recordatorio_prospecto')
                                                ->get();
        return $recordatorios;
    }

    public static function getRecordatoriosUsuarios()
    {
        $now            = date('Y-m-d H:i:s');
        $recordatorios  = RecordatoriosUsuarios::join('users', 'users.id', 'recordatorio_colaborador.id_colaborador')
                                                ->join('users_one_signal', 'users_one_signal.user_id', 'users.id')                                       
                                                ->where('recordatorio_colaborador.status', 0)
                                                ->where('fecha', '<=', $now)
                                                ->groupBy('recordatorio_colaborador.id_recordatorio_colaborador')
                                                ->get();
        return $recordatorios;
    }

    public static function updateRecordatorioOportunidadStatus($recordatorio_oportunidad_id)
    {
        $recordatorio = RecordatoriosOportunidades::find($recordatorio_oportunidad_id);

        if(isset($recordatorio->status)){
            $recordatorio->status = 1;
            $recordatorio->save();
        }
    }

    public static function updateRecordatorioProspectoStatus($recordatorio_prospecto_id)
    {
        $recordatorio = RecordatoriosProspectos::find($recordatorio_prospecto_id);
        
        if(isset($recordatorio->status)){
            $recordatorio->status = 1;
            $recordatorio->save();
        }
    }

    public static function updateRecordatorioUsuarioStatus($recordatorio_usuario_id)
    {
        $recordatorio = RecordatoriosUsuarios::find($recordatorio_usuario_id);

        if(isset($recordatorio->status)){
            $recordatorio->status = 1;
            $recordatorio->save();
        }
    }

}

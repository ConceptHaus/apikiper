<?php
namespace App\Http\Services\Recordatorios;
use App\Http\Repositories\Recordatorios\RecordatoriosRep;
use App\Modelos\Recordatorios\Recordatorios;
use App\Http\Services\OneSignal\OneSignalService;

class RecordatoriosService
{
    public static function getRecordatoriosOportunidades(){
        $recordatorios = RecordatoriosRep::getRecordatoriosOportunidades();
        
        if (count($recordatorios)>0) {
            foreach ($recordatorios as $key => $recordatorio) {
                OneSignalService::sendNotification(
                    $recordatorio['user_id'],
                    'Alerta Oportunidad '. $recordatorio['nombre_oportunidad'],
                    $recordatorio['nota_recordatorio'],
                    'alerta_oportunidad',
                    $recordatorio['id_recordatorio_oportunidad']
                );

                RecordatoriosRep::updateRecordatorioOportunidadStatus($recordatorio['id_recordatorio_oportunidad']);
            }
        }
    }

    public static function getRecordatoriosProspectos(){
        $recordatorios =  RecordatoriosRep::getRecordatoriosProspectos();
         
        if (count($recordatorios)>0) {
            foreach ($recordatorios as $key => $recordatorio) {
                OneSignalService::sendNotification(
                    $recordatorio['user_id'],
                    'Alerta Prospecto '. $recordatorio['nombre'].' '. $recordatorio['apellido'],
                    $recordatorio['nota_recordatorio'],
                    'alerta_prospecto',
                    $recordatorio['id_recordatorio_prospecto']
                );
                
                RecordatoriosRep::updateRecordatorioProspectoStatus($recordatorio['id_recordatorio_prospecto']);
            }
        }
    }

    public static function getRecordatoriosUsuarios(){
         $recordatorios =  RecordatoriosRep::getRecordatoriosUsuarios();

         if (count($recordatorios)>0) {
            foreach ($recordatorios as $key => $recordatorio) {
                OneSignalService::sendNotification(
                    $recordatorio['user_id'],
                    'Alerta Usuario '. $recordatorio['nombre'].' '.$recordatorio['apellido'],
                    $recordatorio['nota'],
                    'alerta_usuario',
                    $recordatorio['id_recordatorio_colaborador']
                );
                RecordatoriosRep::updateRecordatorioUsuarioStatus($recordatorio['id_recordatorio_colaborador']);
            }
        }
    }
}

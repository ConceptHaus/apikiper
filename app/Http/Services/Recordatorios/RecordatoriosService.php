<?php
namespace App\Http\Services\Recordatorios;
use App\Http\Repositories\Recordatorios\RecordatoriosRep;
use App\Modelos\Recordatorios\Recordatorios;
use App\Http\Services\OneSignal\OneSignalService;
use Twilio\Rest\Client;

class RecordatoriosService
{

    public $accountSid = "";
    public $authToken = "";
    public $sendingNumber = "";
    public $twilioClient = "";

    function __construct()
    { 

        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');
        $this->twilioClient = new Client($accountSid, $authToken);
    
    }

    public function enviarRecodatorioSMS($telefono, $mensaje){

       return $this->twilioClient->messages->create(
            '+52'.$telefono,
            array(
                "from" => $this->sendingNumber,
                "body" => 'Kiper reminder: '.$mensaje
            )
        );

    }

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

                // OneSignalService::sendNotification(
                //     $recordatorio['user_id'],
                //     'Alerta Prospecto '. $recordatorio['nombre'].' '. $recordatorio['apellido'],
                //     $recordatorio['nota_recordatorio'],
                //     'alerta_prospecto',
                //     $recordatorio['id_recordatorio_prospecto']
                // );

                if ( strlen( $recordatorio->telefono ) == 10 ) {

                    $sms = RecordatoriosService::enviarRecodatorioSMS($recordatorio->telefono, $recordatorio->nota_recordatorio );
                    if ( $sms ) {
                        RecordatoriosRep::updateRecordatorioProspectoStatus( $recordatorio->id_recordatorio_prospecto );
                    }
                }

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

<?php
namespace App\Http\Services\Recordatorios;
use App\Http\Repositories\Recordatorios\RecordatoriosRep;
use App\Modelos\Recordatorios\Recordatorios;
use App\Http\Services\OneSignal\OneSignalService;
use Twilio\Rest\Client;

class RecordatoriosService
{
    public static function enviarRecodatorioSMS($telefono, $mensaje){

        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $sendingNumber = env('TWILIO_NUMBER');
        $twilioClient = new Client($accountSid, $authToken);

       return $twilioClient->messages->create(
            '+52'.$telefono,
            array(
                "from" => $sendingNumber,
                "body" => 'Kiper reminder '.$mensaje
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
                $aquien_enviar = $recordatorio->aquien_enviar;
                if ( !$aquien_enviar ) {
                    $aquien_enviar = '{"a_mi":false,"colaborador":true,"cliente":false}';
                }

                $enviarList = json_decode( $aquien_enviar );
                foreach ($enviarList as $key => $envia) {
                    try {
                        if($enviarList->$key){
                           
                            $mensaje = "";
                            $telefono = $recordatorio->celular_prospecto;

                           if ($key == 'cliente') {
                                $telefono = $recordatorio->celular_prospecto;
                                $mensaje = $recordatorio->nota_recordatorio;
                           }

                           if ($key == 'colaborador' or $key == "a_mi") {
                                $telefono = $recordatorio->celular_colaborador;
                                $mensaje .= "Nombre Prospecto: ". $recordatorio->prospecto_name;
                                $mensaje .= " Email Prospecto: ". $recordatorio->prospecto_email;
                                $mensaje .= " Mensaje: ". $recordatorio->nota_recordatorio;

                           }

                            if ( strlen( $telefono ) == 10 ) {

                                $sms = RecordatoriosService::enviarRecodatorioSMS( $telefono, $mensaje );
                                if ( $sms ) {
                                    RecordatoriosRep::updateRecordatorioProspectoStatus( $recordatorio->id_recordatorio_prospecto );
                                }
                            }

                        }
                    } catch (Exception $e) {
                        // write to logs error
                    }

                }
                
                #RecordatoriosRep::updateRecordatorioProspectoStatus($recordatorio['id_recordatorio_prospecto']);
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

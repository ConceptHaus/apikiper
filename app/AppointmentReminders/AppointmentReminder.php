<?php 

namespace App\AppointmentReminders;

use Illuminate\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;


use DB;
class AppointmentReminder
{
    /**
     * Construct a new AppointmentReminder
     *
     * @param Illuminate\Support\Collection $twilioClient The client to use to query the API
     */
    function __construct()
    {   
        $now = Carbon::now()->toDateTimeString();
        $inTwentyMinutes = Carbon::now()->addMinutes(20)->toDateTimeString();
        $this->appointments = DB::table('recordatorios_prospecto')
                            ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                            ->join('users','users.id','recordatorios_prospecto.id_colaborador')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->join('prospectos','prospectos.id_prospecto','recordatorios_prospecto.id_prospecto')
                            ->select('users.nombre','detalle_colaborador.whatsapp','prospectos.nombre as nombre_prospecto','detalle_recordatorio_prospecto.nota_recordatorio','detalle_recordatorio_prospecto.fecha_recordatorio')
                            ->whereBetween('detalle_recordatorio_prospecto.fecha_recordatorio',[$now, $inTwentyMinutes])->get();
        
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendReminders(){
        $this->appointments->each(
            function($appointment){
                $this->_remindAbout($appointment);
                //echo $appointment;
            }
        );
    }

    /**
     * Sends a message for an appointment
     *
     * @param Appointment $appointment The appointment to remind
     *
     * @return void
     */
    private function _remindAbout($appointment)
    {
        //$recipientName = $appointment->name;
        // $time = Carbon::parse($appointment->when, 'UTC')
        //       ->subMinutes($appointment->timezoneOffset)
        //       ->format('g:i a');

        $message = "Tienes un recordatorio de >Kiper";
        $this->_sendMessage('+525539487708', $appointment);
    }

    /**
     * Sends a single message using the app's global configuration
     *
     * @param string $number  The number to message
     * @param string $content The content of the message
     *
     * @return void
     */
    private function _sendMessage($number, $content)
    {
        $this->twilioClient->messages->create(
            'whatsapp:+5215539487708',
            array(
                "from" => 'whatsapp:+14155238886',
                "body" => $content
            )
        );
    }

}
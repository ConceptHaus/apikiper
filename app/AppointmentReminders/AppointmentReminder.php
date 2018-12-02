<?php 

namespace App\AppointmentReminders;

use Illuminate\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;
use App\Modelos\Extras\RecordatorioProspecto;
class AppointmentReminder
{
    /**
     * Construct a new AppointmentReminder
     *
     * @param Illuminate\Support\Collection $twilioClient The client to use to query the API
     */
    function __construct()
    {
        $this->appointments = RecordatorioProspecto::AppoinmentsDue()->get();

        
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendReminders(){
        //$this->appointments->each(
        //    function($appointment){
                $this->_remindAbout('prueba');
          //  }
       // );
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
        // $recipientName = $appointment->name;
        // $time = Carbon::parse($appointment->when, 'UTC')
        //       ->subMinutes($appointment->timezoneOffset)
        //       ->format('g:i a');

        $message = "Hello test, this is a reminder that you have an appointment at time!";
        $this->_sendMessage('+525539487708', $message);
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
            $number,
            array(
                "from" => $this->sendingNumber,
                "body" => $content
            )
        );
    }

}
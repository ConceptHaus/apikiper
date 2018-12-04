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

        $this->recordatorios_prospecto = DB::table('recordatorios_prospecto')
                            ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                            ->join('users','users.id','recordatorios_prospecto.id_colaborador')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->join('prospectos','prospectos.id_prospecto','recordatorios_prospecto.id_prospecto')
                            ->select('users.nombre','detalle_colaborador.celular','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','detalle_recordatorio_prospecto.nota_recordatorio','detalle_recordatorio_prospecto.fecha_recordatorio')
                            ->where('recordatorios_prospecto.notification_sent',0)
                            ->whereBetween('detalle_recordatorio_prospecto.fecha_recordatorio',[$now, $inTwentyMinutes])->get();
       
        $this->recordatorios_oportunidades = DB::table('recordatorios_oportunidad')
                            ->join('detalle_recordatorio_op','detalle_recordatorio_op.id_recordatorio_oportunidad','recordatorios_oportunidad.id_recordatorio_oportunidad')
                            ->join('users','users.id','recordatorios_oportunidad.id_colaborador')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->join('oportunidades','oportunidades.id_oportunidad','recordatorios_oportunidad.id_oportunidad')
                            ->select('users.nombre','detalle_colaborador.celular','oportunidades.nombre_oportunidad','detalle_recordatorio_op.nota_recordatorio','detalle_recordatorio_op.fecha_recordatorio')
                            ->where('recordatorios_oportunidad.notification_sent',0)
                            ->whereBetween('detalle_recordatorio_op.fecha_recordatorio',[$now, $inTwentyMinutes])->get();



        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendReminders(){

        foreach($this->recordatorios_prospecto as $reminder){
                $date = Carbon::parse($reminder->fecha_recordatorio)->format('H:i');
                
                DB::beginTransaction();
                $reminder->notification_sent = 1;
                $reminder->save();
                DB::commit();

                $this->twilioClient->messages->create(
                '+52'.$reminder->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Kiper reminder: '.$reminder->nombre.' debes '.$reminder->nota_recordatorio.' con '.$reminder->nombre_prospecto.' '.$reminder->apellido_prospecto.' a las '.$date
                ));
        }

        foreach($this->recordatorios_oportunidades as $reminder){
                $date = Carbon::parse($reminder->fecha_recordatorio)->format('H:i');
                
                DB::beginTransaction();
                $reminder->notification_sent = 1;
                $reminder->save();
                DB::commit();

                $this->twilioClient->messages->create(
                '+52'.$reminder->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Kiper reminder: '.$reminder->nombre.' debes '.$reminder->nota_recordatorio.' con '.$reminder->nombre_oportunidad.' a las '.$date
                ));
        }

        
    }

    


}
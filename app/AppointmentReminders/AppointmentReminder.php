<?php 

namespace App\AppointmentReminders;

use Illuminate\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;

use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\RecordatorioOportunidad;
use App\Modelos\Extras\RecordatorioColaborador;
use Illuminate\Support\Str;
use DB;
use Mailgun;
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
        $inOneDay = Carbon::now()->add(1, 'day')->toDateTimeString();

        $this->recordatorios_prospecto = DB::table('recordatorios_prospecto')
                            ->join('detalle_recordatorio_prospecto','detalle_recordatorio_prospecto.id_recordatorio_prospecto','recordatorios_prospecto.id_recordatorio_prospecto')
                            ->join('users','users.id','recordatorios_prospecto.id_colaborador')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->join('prospectos','prospectos.id_prospecto','recordatorios_prospecto.id_prospecto')
                            ->select('recordatorios_prospecto.id_recordatorio_prospecto','users.email','users.nombre','detalle_colaborador.celular','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','detalle_recordatorio_prospecto.nota_recordatorio','detalle_recordatorio_prospecto.fecha_recordatorio')
                            ->where('recordatorios_prospecto.notification_sent',0)
                            ->whereBetween('detalle_recordatorio_prospecto.fecha_recordatorio',[$now, $inTwentyMinutes])->get();
       
        $this->recordatorios_oportunidades = DB::table('recordatorios_oportunidad')
                            ->join('detalle_recordatorio_op','detalle_recordatorio_op.id_recordatorio_oportunidad','recordatorios_oportunidad.id_recordatorio_oportunidad')
                            ->join('users','users.id','recordatorios_oportunidad.id_colaborador')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->join('oportunidades','oportunidades.id_oportunidad','recordatorios_oportunidad.id_oportunidad')
                            ->select('recordatorios_oportunidad.id_recordatorio_oportunidad','users.email','users.nombre','detalle_colaborador.celular','oportunidades.nombre_oportunidad','detalle_recordatorio_op.nota_recordatorio','detalle_recordatorio_op.fecha_recordatorio')
                            ->where('recordatorios_oportunidad.notification_sent',0)
                            ->whereBetween('detalle_recordatorio_op.fecha_recordatorio',[$now, $inTwentyMinutes])->get();

        $this->recordatorios_colaboradores = DB::table('recordatorio_colaborador')
                            ->join('users', 'recordatorio_colaborador.id_colaborador', 'users.id')
                            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                            ->select('recordatorio_colaborador.id_recordatorio_colaborador','users.email','users.nombre','detalle_colaborador.celular','recordatorio_colaborador.nota','recordatorio_colaborador.fecha', 'recordatorio_colaborador.hora')
                            ->where('recordatorio_colaborador.notification_sent',0)
                            ->wherenull('recordatorio_colaborador.deleted_at')
                            ->whereBetween('recordatorio_colaborador.fecha',[$now, $inOneDay])->get();                            



        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->sendingNumber = env('TWILIO_NUMBER');

        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendReminders(){

        foreach($this->recordatorios_prospecto as $reminder){
                $date = Carbon::parse($reminder->fecha_recordatorio)->format('H:i');
                
                DB::beginTransaction();
                $recordatorio = RecordatorioProspecto::where('id_recordatorio_prospecto',$reminder->id_recordatorio_prospecto)->first();
                $recordatorio->notification_sent = 1;
                $recordatorio->save();
                DB::commit();

                $arrayReminder['nombre'] = $reminder->nombre;
                $arrayReminder['email'] = $reminder->email;
                $arrayReminder['nota_recordatorio'] = $reminder->nota_recordatorio;
                $arrayReminder['nombre_prospecto'] = $reminder->nombre_prospecto;
                $arrayReminder['apellido_prospecto'] = $reminder->apellido_prospecto;
                $arrayReminder['date'] = $date;
                $arrayReminder['link'] = env('URL_FRONT');
                Mailgun::send('mailing.reminders', $arrayReminder, function($contacto) use ($arrayReminder){
                    $contacto->from('reminders@kiper.app', 'Kiper');
                    $contacto->subject('Kiper reminder');
                    $contacto->to($arrayReminder['email'],$arrayReminder['nombre']);
                });
                
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
                $recordatorio = RecordatorioOportunidad::where('id_recordatorio_oportunidad',$reminder->id_recordatorio_oportunidad)->first();
                $recordatorio->notification_sent = 1;
                $recordatorio->save();
                DB::commit();

                $arrayReminder['nombre'] = $reminder->nombre;
                $arrayReminder['email'] = $reminder->email;
                $arrayReminder['nota_recordatorio'] = $reminder->nota_recordatorio;
                $arrayReminder['nombre_oportunidad'] = $reminder->nombre_oportunidad;
                $arrayReminder['date'] = $date;
                $arrayReminder['link'] = env('URL_FRONT');
                Mailgun::send('mailing.reminders_op', $arrayReminder, function($contacto) use ($arrayReminder){
                    $contacto->from('reminders@kiper.app', 'Kiper');
                    $contacto->subject('Kiper reminder');
                    $contacto->to($arrayReminder['email'],$arrayReminder['nombre']);
                });

                $this->twilioClient->messages->create(
                '+52'.$reminder->celular,
                array(
                    "from" => $this->sendingNumber,
                    "body" => 'Kiper reminder: '.$reminder->nombre.' debes '.$reminder->nota_recordatorio.' con '.$reminder->nombre_oportunidad.' a las '.$date
                ));
        }

        foreach($this->recordatorios_colaboradores as $reminder){
            $date = Carbon::parse($reminder->fecha)->format('H:i');
            
            DB::beginTransaction();
            $recordatorio = RecordatorioColaborador::where('id_recordatorio_colaborador',$reminder->id_recordatorio_colaborador)->first();
            $recordatorio->notification_sent = 1;
            $recordatorio->save();
            DB::commit();

            $arrayReminder['nombre'] = $reminder->nombre;
            $arrayReminder['email'] = $reminder->email;
            $arrayReminder['nota_recordatorio'] = $reminder->nota;
            $arrayReminder['nombre_prospecto'] = ' ';
            $arrayReminder['apellido_prospecto'] = ' ';
            $arrayReminder['date'] = $date;
            $arrayReminder['link'] = env('URL_FRONT');
            Mailgun::send('mailing.reminders', $arrayReminder, function($contacto) use ($arrayReminder){
                $contacto->from('reminders@kiper.app', 'Kiper');
                $contacto->subject('Kiper reminder');
                $contacto->to($arrayReminder['email'],$arrayReminder['nombre']);
            });
            
            $this->twilioClient->messages->create(
            '+52'.$reminder->celular,
            array(
                "from" => $this->sendingNumber,
                "body" => 'Kiper reminder: '.$reminder->nombre.' debes '.$reminder->nota.' a las '.$date
            ));
    }

        return response()->json([
            'error'=>false,
            'mensajes_enviados_op'=>DB::table('recordatorios_oportunidad')
                                    ->where('recordatorios_oportunidad.notification_sent',1)->get(),
            'mensajes_enviado_pros'=> DB::table('recordatorios_prospecto')
                                    ->where('recordatorios_prospecto.notification_sent',1)->get()
        ]);
    }

    


}
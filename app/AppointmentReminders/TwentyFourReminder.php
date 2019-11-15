<?php

namespace App\AppointmentReminders;

use Illuminate\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;

use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\RecordatorioOportunidad;
use App\Modelos\Extras\RecordatorioColaborador;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\User;
use Illuminate\Support\Str;
use DB;
use Mailgun;

class TwentyFourReminder
{
    /**
     * Construct a new AppointmentReminder
     *
     * @param Illuminate\Support\Collection $twilioClient The client to use to query the API
     */
    function __construct()
    {
        $this->now = Carbon::now();
        $this->prospectos = DB::table('prospectos')
        ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
        ->join('colaborador_prospecto','prospectos.id_prospecto','colaborador_prospecto.id_prospecto')
        ->select('prospectos.nombre','prospectos.apellido','prospectos.correo','prospectos.created_at','colaborador_prospecto.id_colaborador')
        ->where('status_prospecto.id_cat_status_prospecto',2)->get();
    }

    public function sendReminders(){
        foreach($this->prospectos as $prospecto){
            if($this->now->diffInDays($prospecto->created_at) >= 1){
                
                $user = User::find($prospecto->id_colaborador);
                $admins = User::where('is_admin',1)->get();
                
                $arrayReminder['name_colaborador'] = $user->nombre;
                $arrayReminder['email_colaborador'] = $user->email;
                $arrayReminder['name_prospecto'] = "{$prospecto->nombre} {$prospecto->apellido}";
                $arrayReminder['email_prospecto'] = $prospecto->correo;

                
                Mailgun::send('mailing.reminder_contact',$arrayReminder, function($contacto) use ($arrayReminder){
                    $contacto->from('reminders@kiper.app', 'Kiper');
                    $contacto->subject("¡{$arrayReminder['name_colaborador']}, no lo dejes pasar! 😱");
                    $to->to($arrayReminder['email_colaborador'],$arrayReminder['name_colaborador']);
                });
            }

        }
    }
}
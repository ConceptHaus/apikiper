<?php

namespace App\Listeners;

use App\Events\NewLead;
use App\Modelos\User;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mailgun;
use DB;

class NewCallListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
        
    }

    /**
     * Handle the event.
     *
     * @param  NewCall  $event
     * @return void
     */
    public function handle($event){
        $actividad = $event->evento;
        $admins = DB::table('users')->where('super_admin',1)->get();
        
        //echo $admins;
        
        //Array de admins
        if(isset($actividad->campaign->utm_campaign)){
            if(strpos($actividad->campaign->utm_campaign,'cancol') !== false){
            $admins = DB::table('users')->where('email','ian@co-labora.mx')->get();
            
            }
            if(strpos($actividad->campaign->utm_campaign,'gdlcol') !== false){
                $admins = DB::table('users')->where('email','paola@co-labora.mx')->get();
                
            }
        }
        
        $array_admins = array();
        foreach ($admins as $admin) {
            array_push($array_admins,[$admin->email=>['name'=>$admin->nombre.' '.$admin->apellido]]);
        }
        if(count($admins)>0){

            $data['email'] = $array_admins;
            $data['asunto'] = 'Un nuevo prospecto te ha llamado ☎️ 😁 🎉';
            $data['email_de'] = 'activity@kiper.app';
            $data['nombre_de'] = 'Kiper';

            
           // echo $actividad->nombre;

            $data['nombre_p'] = $actividad->nombre;
            $data['telefono_p'] = $actividad->detalle_prospecto->telefono;
            $data['recording_p'] = $actividad->calls->play_recording;
            $data['campaign_p'] = (isset($actividad->campaign->utm_campaign) ? $actividad->campaign->utm_campaign : 'orgánico');
            $data['term_p'] = (isset($actividad->campaign->utm_term) ? $actividad->campaign->utm_term : 'orgánico');



            //Template
            //Funcion for para array de admins

            Mailgun::send('mailing.template_newcall',$data, function($message) use ($data){
                $message->from($data['email_de'],$data['nombre_de']);
                $message->subject($data['asunto']);
                foreach($data['email'] as $to_){
                    $message->to($to_);
                }
                $message->trackOpens(true);
                $message->tag('new_lead');
            });
        }
    }

}
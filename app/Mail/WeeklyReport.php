<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $attachment;
    public function __construct($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from('activity@kiper.io','Kiper')->subject('Reporte Semanal 30/03/2020 - 05/04/2020 | Kiper')->view('mailing.report');
                 
        foreach($this->attachment as $attach){
            $email->attach($attach);
        }
        
        return $email;
    }   
}

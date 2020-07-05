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
        $email = $this->from('activity@kiper.app','Kiper')->subject('Reporte Semanal 28/06/2020 - 05/07/2020 | Kiper')->view('mailing.report');
                 
        foreach($this->attachment as $attach){
            $email->attach($attach);
        }
        
        return $email;
    }   
}

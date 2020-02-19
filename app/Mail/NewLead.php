<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modelos\User;

class NewLead extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->data->desarrollo == 'polanco'){
            $this->data->admin = User::where('rol',1)->get();
        }
        else if($this->data->desarrollo == 'napoles'){
            $this->data->admin = User::where('rol',2)->get();
        }
        $this->data->admin = User::where('rol',1)->get();
        return $this->subject("Nuevo prospecto vía {$this->data->fuente->nombre} 🎉")
                    ->from('activity@kiper.io','Kiper')
                    ->cc($this->data->admin)
                    ->view('mailing.newlead');
    }
}

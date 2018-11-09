<?php 

namespace App\Http\Controllers\Integraciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google;
use LaravelGmail;
use Dacastro4\LaravelGmail\Services\Message\Mail;

class GoogleController extends Controller
{

public function googleApi(){

    return LaravelGmail::redirect();
    
}

public function googleApiCallback(){

    
    LaravelGmail::makeToken();
    // $id = '166f4226948f3c73';
    // $message = LaravelGmail::message()->get($id);
    
    $mail = new Mail;
    $mail->to('sergirams@gmail.com')->from('sergio@concepthaus.mx')->subject('Prueba')->message('Prueba')->send();
    

    return response()->json($mail);

        // foreach ( $messages as $message ) {
        //     $mensaje = LaravelGmail::message()->get($message->id);

        //     $body = $mensaje->getSubject();
        //     $subject = $mensaje->getSubject();

        //     return response()->json($body);
        // }
        //return $messages;
    

}


}
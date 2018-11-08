<?php 

namespace App\Http\Controllers\Integraciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google;
use LaravelGmail;

class GoogleController extends Controller
{

public function googleApi(){

    return LaravelGmail::redirect();
    
}

public function googleApiCallback(){

    
    LaravelGmail::makeToken();
    $id = '166f4226948f3c73';
    $message = LaravelGmail::message()->get($id);

    return response()->json($message->getPlainTextBody);

}


}
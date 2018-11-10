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

public function googleApiCallback(Request $request){

    
    $client = new \Google_Client([
        'client_id' => '764823240722-ef1gloj1rb8f7i56v6a7umbo8feqqpg9.apps.googleusercontent.com'
    ]);
    
    $payload = $client->verifyIdToken($request->idToken);

    return response()->json($payload);
}


}
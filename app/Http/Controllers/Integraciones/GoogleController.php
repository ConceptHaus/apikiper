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

    return LaravelGmail::makeToken();

}


}
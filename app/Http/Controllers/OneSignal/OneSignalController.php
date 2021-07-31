<?php

namespace App\Http\Controllers\OneSignal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\OneSignal\OneSignalService;

class OneSignalController extends Controller
{
   
    public function signUp(Request $request)
    {
        return OneSignalService::signUp($request->user_id, $request->player_id);
    }

    public function signOff(Request $request)
    {
        return OneSignalService::signOff($request->user_id, $request->player_id);
    }

    public function sendNotification(Request $request){
        return OneSignalService::sendNotification($request->user_id, $request->message);
    }

}

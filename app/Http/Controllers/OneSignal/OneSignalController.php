<?php

namespace App\Http\Controllers\OneSignal;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;
use App\Http\Services\OneSignal\OneSignalService;

class OneSignalController extends Controller
{
    public $authService;

    public function __construct(
        AuthService $auth
    ) {
        $this->authService = $auth;
    }
   
    public function signUp(Request $request)
    {
        return OneSignalService::signUp($this->authService->getCurrentUserId(), $request->player_id);
    }

    public function signOff(Request $request)
    {
        return OneSignalService::signOff($this->authService->getCurrentUserId(), $request->player_id);
    }

    // TODO msepulveda - Comentar esta funcion ya que es solo para pruebas
    public function sendNotification(Request $request){
        return OneSignalService::sendNotification($request->user_id, $request->title, $request->message, $request->notification_type, $request->data);
    }

}

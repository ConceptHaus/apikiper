<?php
namespace App\Http\Services\Auth;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function getUserAuthInfo($rol){
        return Auth::guard()->user();
    }
}

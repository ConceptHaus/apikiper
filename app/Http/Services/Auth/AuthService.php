<?php
namespace App\Http\Services\Auth;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function getUserAuthInfo(){
        return Auth::guard()->user();
    }

    public function getCurrentUserId(){
        return $this->getUserAuthInfo()->id;
    }
}

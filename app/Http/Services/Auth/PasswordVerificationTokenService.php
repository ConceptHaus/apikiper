<?php

namespace App\Http\Services\Auth;

#use App\Modelos\User;
#use App\Http\Repositories\Users\UsersRep;

class PasswordVerificationTokenService
{

    private $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

}

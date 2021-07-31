<?php

namespace App\Http\Services\Password;

use App\Modelos\PasswordRecovery;
use App\Modelos\User;
use App\Http\Services\Users\UserService;
use App\Http\Services\Password\PasswordRecoveryService;
use App\Http\Exceptions\Auth\UserNotFoundException;
use App\Http\Exceptions\Auth\VerificationTokenNotFoundException;

class PasswordManagementService
{

    private $userService;
    private $passwordRecoveryService;
    private $passwordVerificationTokenService;
    private $passwordRecoverySender;

    public function __construct(
        UserService $userService, 
        PasswordRecoveryService $passwordRecoveryService,
        PasswordVerificationTokenService $passwordVerificationTokenService,
        PasswordRecoverySender $passwordRecoverySender
    ){
        $this->userService = $userService;
        $this->passwordVerificationTokenService = $passwordVerificationTokenService;
        $this->passwordRecoverySender = $passwordRecoverySender;
    }

    public generatePasswordRecoveryToken(String email) throws AccountNotFoundException {
        $user = $this->userService->findByEmail(email);
        if($user == null) 
            throw new UserNotFoundException("forgotPassword.error.user.notFound");

        $passwordRecovery = $this->passwordVerificationTokenService.createPasswordRecovery($user);
        $this->passwordRecoverySender->sendPasswordRecovery($user->email, $passwordRecovery->veritifationToken);
    }

    public changePasswordByToken($verificationToken, $password){
        $passwordRecovery = $this->passwordRecoveryService->findByToken(verificationToken);
        if($passwordRecovery == null) 
            throw new VerificationTokenNotFoundException("forgotPassword.error.token.notFound");
        
        $user = $userService->findById($passwordRecovery->user_id);
        $user->password = bcrypt($password);
        $userService->save($user);

        $passwordRecoveryService->delete($passwordRecovery);
    }

}

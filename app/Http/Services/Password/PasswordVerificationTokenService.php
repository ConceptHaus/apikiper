<?php

namespace App\Http\Services\Password;

use App\Modelos\PasswordRecovery;
use App\Http\Services\Password\PasswordRecoveryService;

class PasswordVerificationTokenService
{

    private $passwordRecoveryService;

    public function __construct(PasswordRecoveryService $passwordRecoveryService){
        $this->passwordRecoveryService = $passwordRecoveryService;
    }

    public function createPasswordRecovery($user) {
        $passwordRecovery = $this->passwordRecoveryService->findByUser($user);
        if($passwordRecovery != null) $this->passwordRecoveryService->delete($passwordRecovery);

        $passwordRecovery = new PasswordRecovery();
        $passwordRecovery->user_id = $user->id;
        $passwordRecovery->verificationToken = str_random(16);
        return $this->passwordRecoveryService->save($passwordRecovery);
    }

}

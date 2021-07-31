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

    public createPasswordRecovery($user) {
        $passwordRecovery = $this->passwordRecoveryService.findByUser($user);
        if ($passwordRecovery == null) {
            $passwordRecovery = new PasswordRecovery();
            $passwordRecovery->user_id($user->id);
        }
        $passwordRecovery.setVerificationToken(str_random(8));
        return $this->passwordRecoveryService->save($passwordRecovery);
    }

}

<?php

namespace App\Http\Services\Auth;

#use App\Modelos\User;
#use App\Http\Repositories\Users\UsersRep;
use App\Http\Services\Users\UserService;
use App\Http\Exceptions\Auth\UserNotFoundException;

class PasswordManagementService
{

    private $userService;
    private $passwordVerificationTokenService;

    public function __construct(
        UserService $userService, 
        PasswordVerificationTokenService $passwordVerificationTokenService
    ){
        $this->userService = $userService;
        $this->passwordVerificationTokenService = $passwordVerificationTokenService;
    }

    public generatePasswordRecoveryToken(String email) throws AccountNotFoundException {

        $user = $this->userService->findByEmail(email);
        if($user == null) throw new UserNotFoundException("forgotPassword.error.user.notFound");

        $passwordRecovery = $this->passwordVerificationTokenService.createPasswordRecovery($user);

        String accountVerificationLink =
                this.passwordVerificationTokenService.createPasswordRecoveryUrl(
                        passwordRecovery.getVerificationToken(), platformId);

        passwordRecoveryRep.save(passwordRecovery);

        try {
            passwordRecoverySender.sendPasswordRecovery(appAccount.getEmail(),
                    accountVerificationLink, platformId, request);
        } catch (Exception e) {
            logger.error(this.getClass().getSimpleName()+".generatePasswordRecoveryToken", e);
        }
    }

}

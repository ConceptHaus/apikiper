<?php

namespace App\Http\Services\Password;

use App\Modelos\PasswordRecovery;
use App\Http\Services\Password\PasswordRecoveryService;

class PasswordRecoverySender{

    public function sendPasswordRecovery($email, $token){
        /*
        TODO - Integrar envio de correo
        emailService.sendMessage(
            ixMessages.at(request, "account.password.recovery.subject." + platformId),
            this.buildHtmlContent(activationLink, platformId, request),
            email
        );
        */
    }

}

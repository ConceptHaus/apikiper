<?php

namespace App\Http\Services\Password;

use App\Modelos\PasswordRecovery;
use App\Http\Services\Password\PasswordRecoveryService;
use Mailgun;

class PasswordRecoverySender{

    public function sendPasswordRecovery($user, $token){
        
        $msg = array(
            'subject'      => "Recuperación de Contraseña",
            'email'        => $user->email,
            'colaborador'  => $user->nombre ." ". $user->apellido,
            'token'        => $token,
        );

        Mailgun::send('auth.emails.recover-password', ['msg' => $msg], function ($m) use ($msg){
            $m->to($msg['email'], $msg['colaborador'])->subject($msg['subject']);
            $m->from('notificaciones@kiper.com.mx', 'Kiper');
});

    }

}

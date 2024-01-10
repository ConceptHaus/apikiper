<?php

namespace App\Http\Repositories\Password;

use App\Modelos\PasswordRecovery;

class PasswordRecoveryRep{

    public function save($model){
        $model->save();
        return $model;
    }

    public function delete($model){
        $model->delete();
    }

    public function findByUser($user){
        return PasswordRecovery::where('user_id', '=', $user->id)->first();
    }

    public function findByToken($token){
        return PasswordRecovery::where('verificationToken', '=', $token)->first();
    }

}

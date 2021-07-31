<?php

namespace App\Http\Repositories\Password;

use App\Modelos\PasswordRecovery;

class PasswordRecoveryRep{

    public function save($model){
        $model->save();
    }

    public function delete($model){
        $model->delete();
    }

    public function findByUser($user_id){
        return PasswordRecovery::where('user_id', '=', $user_id)->get()->first();
    }

    public function findByUserToken($token){
        return PasswordRecovery::where('veritifationToken', '=', $token)->get()->first();
    }

}

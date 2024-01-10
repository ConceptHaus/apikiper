<?php

namespace App\Http\Repositories\OneSignal;

use App\Modelos\OneSignal\UserOneSignal;

class OneSignalRep
{
    public static function signUp($user_id, $player_id)
    {
        $user_one_signal = UserOneSignal::where('player_id', $player_id)->where('user_id', $user_id)->first();
        if(!isset($user_one_signal->player_id)){
            $new_user_one_signal            = new UserOneSignal;
            $new_user_one_signal->user_id   = $user_id;
            $new_user_one_signal->player_id = $player_id;
            $new_user_one_signal->save();
            return true;
        }else{
            return false;
        }
    }
        

    public static function signOff($user_id, $player_id)
    {
        $user_one_signal = UserOneSignal::where('player_id', $player_id)->where('user_id', $user_id)->first();
        if(isset($user_one_signal->player_id)){
            $user_one_signal->delete();
            return true;
        }

        return false;
    }

}

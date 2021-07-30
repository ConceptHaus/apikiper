<?php
namespace App\Http\Services\OneSignal;
use App\Http\Repositories\OneSignal\OneSignalRep;

class OneSignalService
{
    public static function signUp($user_id, $player_id)
    {
        
        if(!is_null($user_id) AND !is_null($player_id)){
            $new_one_signal_user = OneSignalRep::signUp($user_id, $player_id);
            if($new_one_signal_user){
                return response()->json([
                    'error' => false,
                    'data'  => $new_one_signal_user,
                    'message'   => 'New OneSignal Record was succesfully created'
                ],200);
            }else{
                return response()->json([
                    'error'     => true,
                    'data'      => [],
                    'message'   => 'Player ID already taken'
                ],400);     
            }
        }else{
            return response()->json([
                'error'     => true,
                'data'      => [],
                'message'   => 'Invalid data'
            ],400); 
        }
    }

    public static function signOff($user_id, $player_id)
    {
        
        if(!is_null($user_id) AND !is_null($player_id)){
            $one_signal_user = OneSignalRep::signOff($user_id, $player_id);
            if($one_signal_user){
                return response()->json([
                    'error' => false,
                    'data'  => [],
                    'message'   => 'OneSignal USer was succesfully deleted'
                ],200);
            }else{
                return response()->json([
                    'error'     => true,
                    'data'      => [],
                    'message'   => "OneSignal USer wasn't succesfully deleted"
                ],400);    
            }
        }else{
            return response()->json([
                'error'     => true,
                'data'      => [],
                'message'   => 'Invalid data'
            ],400); 
        }
    }
}

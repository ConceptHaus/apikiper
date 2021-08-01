<?php
namespace App\Http\Services\OneSignal;
use App\Http\Repositories\OneSignal\OneSignalRep;
use App\Modelos\OneSignal\UserOneSignal;

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

    public static function sendNotification($user_id, $title, $message, $notification_type, $data)
    {
        $one_signal_user = UserOneSignal::where('user_id', $user_id)->get();
        
        if (count($one_signal_user) > 0) {
            
            $player_ids  = array();

            foreach ($one_signal_user as $key => $device) {
                $player_ids[] = $device->player_id;
            }
        
            $content = array(
                "en" => $message,
                "es" => $message,
                );
            
                //TODO msepulveda - poner datos en enviroment
            $fields = array(
                'app_id' => "7eaa665b-101e-40b1-9594-2fc40887c776",
                // 'app_id' => env("ONE_SIGNAL_APP_ID"),
                'include_player_ids' => $player_ids,
                'headings' => $title,
                'contents' => $content,
                'data' => array("type" => $notification_type, "data" => $data)             
            );
            
            $fields = json_encode($fields);
            print("\nJSON sent:\n");
            print($fields);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response;
        }
    }
}

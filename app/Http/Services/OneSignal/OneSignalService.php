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

    public static function sendNotification($user_id, $message)
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
            
            $fields = array(
                'app_id' => "7eaa665b-101e-40b1-9594-2fc40887c776",
                // 'app_id' => env("ONE_SIGNAL_APP_ID"),
                // 'include_player_ids' => array("bf1030ae-7363-4a2b-ba6b-bff4dbaa9099", "ff5b2f52-8775-4ae3-97ef-9fefec0482a9", "3841b471-33f8-4aa0-bd5a-dce69a2b5f54", "0bb38922-d83f-4cfd-80f9-56751e91c579", "76970a3f-ff99-449f-b88c-68e1b6822e47"),
                'include_player_ids' => $player_ids,
                'data' => array("foo" => "bar"),
                'contents' => $content
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

<?php

namespace App\BridgeCommands;

use App\Modelos\User;
use Illuminate\Http\Request;
use App\Http\Requests;

use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class CheckUsers
{
    public function saveUsers(){
        $users = DB::table('users')->select('*')->get();
        $current_url=\Request::root().'/api/v1/';
        $client = new Client();
        $url = env('MASTER_API');
        $body["form_params"]["users"] = json_encode($users);
        $body["form_params"]["url_api"]='http://apikiper.test/api/v1/';
        $request = $client->post($url.'kiper/bridge',$body);
        
        return response()->json(['message'=>'ok']);
    }
} 
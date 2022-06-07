<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\CalendarLinks\Link;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;

use App\Http\Enums\OldRole;
use App\Http\Services\Users\UserService;
use App\Http\Services\Roles\RolesService;
use App\Modelos\Role;

use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;

class whatsappController extends Controller
{

    private $userServ;
    private $roleServ;

    public function __construct(){
    }

    public function getMensajes(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.ultramsg.com/instance4088/messages?token=yv47t9lpd8ruvou6&page=1&limit=30&status=all&sort=desc",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }

    }
}
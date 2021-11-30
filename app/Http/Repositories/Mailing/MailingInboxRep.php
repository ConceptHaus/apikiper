<?php

namespace App\Http\Repositories\Mailing;

use App\Modelos\Mailing\Inbox;

class MailingInboxRep
{
    
    public static function getUsername($id){
        $username = "";
        $account = Inbox::find($id);
        if(isset($account->username)){
            $username = $account->username;
        }
        return $username;
    }

    public static function getPassword($id){
        $password = "";
        $account = Inbox::find($id);
        if(isset($account->password)){
            $password = $account->password;
        }
        return $password;
    }

    public static function getHost($id){
        $host = "";
        $account = Inbox::find($id);
        if(isset($account->host)){
            $host = $account->host;
        }
        return $host;
    }

    public static function getPort($id){
        $port = "";
        $account = Inbox::find($id);
        if(isset($account->port)){
            $port = $account->port;
        }
        return $port;
    }

    public static function getEncryption($id){
        $porencryptiont = "";
        $account = Inbox::find($id);
        if(isset($account->encryption)){
            $encryption = $account->encryption;
        }
        return $encryption;
    }

}

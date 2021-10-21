<?php

namespace App\Http\Repositories\MailingInbox;

use App\Modelos\Mailing\Inbox;
use App\Modelos\Mailing\InboxMessages;
use DB;
use Log;
use Crypt;

class MailingInboxRep
{
    public static function getCredentials($user_id)
    {
        return Inbox::where('user_id', $user_id)->first();
    }

    public static function updateCredentials($user_id, $password, $host, $port)
    {
        $credentials            = MailingInboxRep::getCredentials($user_id);
        $credentials->password  = Crypt::encryptString($password);
        $credentials->host      = $host;
        $credentials->port      = $port;
        $credentials->save();
    }

    public static function setCredentials($user_id, $password, $host, $port)
    {
        $credentials                = new Inbox;
        $credentials->user_id       = $user_id;
        if($password != ""){
            $credentials->password  = Crypt::encryptString($password);
        }
        $credentials->host          = $host;
        $credentials->port          = $port;
        $credentials->save();
    }

    public static function unsetCredentials($user_id)
    {
        Inbox::where('user_id', $user_id)->delete();
    }

    public static function getAccount($user_id)
    {
        return Inbox::where('user_id', $user_id)->first();
    }

    public static function getResponse($user_id, $email_id)
    {
        return InboxMessages::where('user_id', $user_id)->where('email_id', $email_id)->get()->toArray();
    }
    
    public static function createResponse($response)
    {
        $message                = new InboxMessages;
        $message->user_id       = $response['user_id'];
        $message->email_id      = $response['sent']."|".$response['email_para'];
        $message->body_message  = $response['contenido'];
        $message->save();
    }

}

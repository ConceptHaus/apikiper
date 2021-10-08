<?php

namespace App\Http\Repositories\MailingInbox;

use App\Modelos\Mailing\Inbox;
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
        $credentials            = new Inbox;
        $credentials->user_id   = $user_id;
        $credentials->password  = Crypt::encryptString($password);
        $credentials->host      = $host;
        $credentials->port      = $port;
        $credentials->save();
    }

    public static function unsetCredentials($user_id)
    {
        Inbox::where('user_id', $user_id)->delete();
    }


}

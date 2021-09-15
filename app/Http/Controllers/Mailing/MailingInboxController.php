<?php

namespace App\Http\Controllers\Mailing;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;
use App\Modelos\Mailing\Inbox;
use App\Modelos\Role;


class MailingInboxController extends Controller
{
    public function getAccount(){
        
        $id = 1;

        $account = Inbox::find($id);

        if(isset($account->id)){
            $client = \Webklex\IMAP\Facades\Client::make([
                'host'          => $account->host,
                'port'          => $account->port,
                'encryption'    => $account->encryption,
                'validate_cert' => true,
                'username'      => $account->username,
                'password'      => $account->password,
                'protocol'      => 'imap'
            ]);

            /* Alternative by using the Facade
            // $client = \Webklex\IMAP\Facades\Client::account('cuenta_1');
            */

            //Connect to the IMAP Server
            $client->connect();

            $folders = $client->getFolders();
            return $folders;

            // $oFolder = $client->getFolder('INBOX');
            // $aMessage = $oFolder->query()->unseen()->limit(10)->get();
            // return $aMessage;
        }
    }

    public function testDB()
    {
        return Role::all();
    }

}

<?php

namespace App\Http\Controllers\Mailing;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;

use App\Modelos\Mailing\Inbox;
use App\Modelos\Role;
use App\Modelos\User;
use App\Http\Services\MailingInbox\MailingInboxService;

use Auth;
use Crypt;

class MailingInboxController extends Controller
{
    
    public function __construct()
    {
        $this->cipher = 'AES-256-CBC';
        $this->key    = env('APP_KEY');
    }

    public function getCredentials()
    {
        $colaborador_id = Auth::user()->id;

        $colaborador = User::find($colaborador_id);
        if(isset($colaborador->id)){
            $credentials = MailingInboxService::getCredentials($colaborador->id);
            if(isset($credentials->password)){
                
                $decrypted_pasword = Crypt::decryptString($credentials->password);
                $credentials->password_length = mb_strlen($decrypted_pasword);
                $credentials->decrypted_pasword = $this->getDummyPassword($credentials->password_length);
                $credentials->email = $colaborador->email;

                return response()->json([
                    'error' => false,
                    'data'  => ["credentials" => $credentials],
                ],200);
            }else{
                return response()->json([
                    'error' => false,
                    'data'  => ["credentials" => $colaborador],
                ],200);
            }
            
        }

        return response()->json([
            'error' => true,
            'message'  => "Credenciales no encontradas",
        ],200);
    }

    public function setCredentials(Request $request)
    {
        $colaborador_id = Auth::user()->id;

        $colaborador = User::find($colaborador_id);
       
        if(isset($colaborador->id)){
            
            $credentials = MailingInboxService::getCredentials($colaborador->id);
            
            //Exisiting Crdentials
            if(isset($credentials->password)){
                MailingInboxService::updateCredentials($colaborador->id, $request->password, $request->host, $request->port);
                return response()->json([
                    'error' => false,
                    'message'  => "Credenciales actualizadas con éxito",
                ],200);
            }
            //New Credentials
            else{
                MailingInboxService::setCredentials($colaborador->id, $request->password, $request->host, $request->port);
                return response()->json([
                    'error' => false,
                    'message'  => "Credenciales registradas con éxito",
                ],200);
            }
        }
    }

    public function unsetCredentials()
    {
        $colaborador_id = Auth::user()->id;

        $colaborador = User::find($colaborador_id);
       
        if(isset($colaborador->id)){
            
            $credentials = MailingInboxService::getCredentials($colaborador->id);
            
            //Exisiting Crdentials
            if(isset($credentials->password)){
                MailingInboxService::unsetCredentials($colaborador->id);
                return response()->json([
                    'error' => false,
                    'message'  => "Credenciales eliminadas con éxito",
                ],200);
            }
        } 
    }

    public function getAccount()
    {
        
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

            // $folders = $client->getFolders();
            // return $folders;

            // $oFolder = $client->getFolder('INBOX');
            // $aMessage = $oFolder->query()->unseen()->limit(10)->get();
            // return $aMessage;





            /** @var \Webklex\IMAP\Folder $oFolder */
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $oFolder = $client->getFolder('INBOX');
            $paginator = $oFolder->search()
            ->since(\Carbon::now()->subDays(14))->get()
            ->paginate($perPage = 5, $page = null, $pageName = 'imap_blade_example');

            return $paginator;

        }
    }

    public function getDummyPassword($amount)
    {
        $password = "";
        for ($i=1; $i <= $amount ; $i++) { 
            $password = $password . "*";
        }
        return $password;
    }

    public function testDB()
    {
        // return Role::all();

    }

    public function getFonts()
    {
        // AIzaSyDI_uOOjfOFe8o_Vb9xxJ-WtPEKF-39fL0
        $a = Crypt::encryptString("111moises");
        // return $a;
        // return $this->decrypt($a);
        return Crypt::decryptString($a);

        return response()->json([
            'error' => false,
            'data'  => $this->decrypt($a),
        ],200);

        $colaborador_id = "0e940a0c-c474-3463-bceb-0db0ad1fd42b";

        $colaborador = User::find($colaborador_id);
        if(isset($colaborador->id)){
            $credentials = MailingInboxService::getCredentials($colaborador->id);
            if(isset($credentials[0]->password)){
                
                $decrypted_pasword =  Crypt::decryptString($credentials[0]->password);
                // return $decrypted_pasword;
                $credentials[0]->password_length = strlen($decrypted_pasword);
                $credentials[0]->decrypted_pasword = $this->getDummyPassword($credentials[0]->password_length);
                $credentials[0]->email = $colaborador->email;
                
                // return $credentials[0];

                return response()->json([
                    'error' => false,
                    'data'  => ["credentials" => $credentials[0]],
                ],200);

            }
        }
    }

}

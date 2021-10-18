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

    public function getAccount($page_number)
    {
        // $colaborador_id = Auth::user()->id;
        $colaborador_id = '0e940a0c-c474-3463-bceb-0db0ad1fd42b';
        
        $colaborador = User::find($colaborador_id);
        if(isset($colaborador->id)){

            $account =  MailingInboxService::getAccount($colaborador_id);
            
            if(isset($account->password)){
                
                $account->username = $colaborador->email;

                $account->password = Crypt::decryptString($account->password);

                // return $account;
                
                // try {
                //     $oClient->connect();
                // }catch (\Webklex\IMAP\Exceptions\ConnectionFailedException $e){
                //     dd("Whoops: ".$e->getMessage());
                // }
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
                // $client->connect();

                // try {
                    $client->connect();
                // }catch (\Webklex\IMAP\Exceptions\ConnectionFailedException $e){
                //     // dd("Whoops: ".$e->getMessage());
                //     return "Error de Conexión";
                // }
                
                $status = $client->isConnected();
                
                // $folders = $client->getFolders();
                // return $folders;
    
                // $oFolder = $client->getFolder('INBOX');
                // $aMessage = $oFolder->query()->unseen()->limit(10)->get();
                // return $aMessage;
    
    
    
    
    
                /** @var \Webklex\IMAP\Folder $oFolder */
                /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */

                // $page_number =  (!is_null($request->page_number)) ? $request->page_number : NULL;
                $oFolder     =  $client->getFolder('INBOX');

                
                $paginator   =  $oFolder->search()
                                        ->since(\Carbon::now()->subDays(14))->get()
                                        ->paginate($perPage = 10, $page = $page_number, $pageName = 'imap_inbox_table');
    
                // return $paginator;


                //In case of no error
                $paginated_messages = array();

                if($paginator->count() > 0){
                
                    $messages = array();
                    foreach($paginator as $oMessage){
                        $message['UID']         = $oMessage->getUid ();
                        // $message['subject']  = $oMessage->getSubject();
                        $message['subject']     = utf8_decode(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject)));
                        $message['from']        = $oMessage->getFrom()[0]->mail;
                        $message['attachments'] = $oMessage->getAttachments()->count() > 0 ? true : false;
                        $flags                  = $oMessage->getFlags();
                        $message['seen']        = (isset($flags['seen']) AND $flags['seen'] == "Seen") ? true : false ;
                        $message['date']        = mb_decode_mimeheader($oMessage->date);
                        // $message['from_name']   = utf8_decode(mb_decode_mimeheader($oMessage->fromaddress ));
                        $message['from_name']   = mb_decode_mimeheader($oMessage->fromaddress );
                        $message['response']    = MailingInboxService::getResponse($colaborador_id, $oMessage->getUid());
                        $message['reply']       = (is_null($message['response'])) ? false : true;
                        $message['owner']       = $colaborador->nombre. " ". $colaborador->apellido;
                        $message['html']        = $oMessage->getTextBody();
                        $messages[]             = $message;
                    }

                    $paginated_messages['messages'] = $messages;
                    $paginated_messages['next']     = (!is_null($page_number)) ? $page_number + 1 : 2;
                    $paginated_messages['prev']     = (!is_null($page_number) AND $page_number > 1) ? $page_number - 1 : 0;
                    
                    return response()->json([
                        'error'     => false,
                        'paginator' => $paginated_messages,
                    ],200);
                }
                else{
                    return response()->json([
                        'error'     => true,
                        'paginator' => [],
                        'message'   => "No hay mensajes en la bandeja de entrada."
                    ],200);
                }

                
        
                
            }else{
                return response()->json([
                    'error'     => true,
                    'paginator' => [],
                    'message'   => "Credenciales no han sido configuradas para el usuario."
                ],401);
            }
        }else{
            return response()->json([
                'error'     => true,
                'paginator' => [],
                'message'   => "Usuario no encontrado"
            ],401);
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


}

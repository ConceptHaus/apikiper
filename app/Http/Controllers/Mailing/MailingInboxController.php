<?php

namespace App\Http\Controllers\Mailing;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Modelos\Mailing\Inbox;
use App\Modelos\Role;
use App\Modelos\User;
use App\Http\Services\MailingInbox\MailingInboxService;

use Auth;
use Crypt;
use Mailgun;

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
            
            //Exisiting Credentials
            if(isset($credentials->user_id)){
                MailingInboxService::updateCredentials($colaborador->id, $request->new_password, $request->host, $request->port);
                return response()->json([
                    'error' => false,
                    'message'  => "Credenciales actualizadas con éxito",
                ],200);
            }
            //New Credentials
            else{
                MailingInboxService::setCredentials($colaborador->id, $request->new_password, $request->host, $request->port);
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
        ini_set('memory_limit', '-1');
        
        $colaborador_id = Auth::user()->id;
        // $colaborador_id = '0e940a0c-c474-3463-bceb-0db0ad1fd42b';
        
        $colaborador = User::find($colaborador_id);
        if(isset($colaborador->id)){

            $account =  MailingInboxService::getAccount($colaborador_id);
            
            if(isset($account->password)){
                
                // return $account;
                
                $username = (!is_null($account->alt_email)) ? $account->alt_email : $colaborador->email;

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
                    'username'      => $username,
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

                // $paginator   =  $oFolder->search()
                //                         ->since(\Carbon::now()->subDays(30))->get()
                //                         // ->since(\Carbon::now())->get()
                //                         ->paginate($perPage = 10, $page = $page_number, $pageName = 'imap_inbox_table');

                $paginator = $oFolder->query()->all()->setFetchOrder("desc")->limit($limit = 10, $page = $page_number)->get();
                
                // return $paginator;


                //In case of no error
                $paginated_messages = array();

                if($paginator->count() > 0){
                    
                   
                    $messages = array();
                    foreach($paginator as $oMessage){
                        
                        $message['UID']             = $oMessage->getUid ();
                        $input = str_replace("_", " ", mb_decode_mimeheader($oMessage->subject));
                        if (iconv('UTF-8', 'UTF-8', $input) != $input) {
                            $message['subject']         =   "kkkkkk";
                            
                        }else{
                            $message['subject']         = utf8_decode(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject)));    
                        }
                        // $message['subject']         = mb_convert_encoding(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject)), 'UTF-8', 'auto');
                        // $message['subject']         = $this->utf8convert(utf8_decode(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject))));
                        // $message['subject']         = utf8_decode(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject)));
                        // $message['subject']         = mb_convert_encoding(mb_decode_mimeheader($oMessage->subject), 'UTF-8', 'UTF-8');
                        $message['from']            = $oMessage->getFrom()[0]->mail;
                        $message['has_attachments'] = $oMessage->getAttachments()->count() > 0 ? true : false;
                        $message['attachments']     = ($message['has_attachments']) ? $oMessage->getAttachments() : [];
                        $flags                      = $oMessage->getFlags();
                        $message['seen']            = (isset($flags['seen']) AND $flags['seen'] == "Seen") ? true : false ;
                        $message['date']            = mb_decode_mimeheader($oMessage->date);
                        $message['from_name']       = mb_decode_mimeheader($oMessage->fromaddress );
                        $message['response']        = MailingInboxService::getResponse($colaborador_id, $message['date']."|".$message['from']);
                        $message['responses']       = count($message['response']) + 1;
                        $message['reply']           = (count($message['response']) > 0) ? true : false;
                        $message['owner']           = $colaborador->nombre. " ". $colaborador->apellido;
                        $message['html']            = ($oMessage->hasHTMLBody()) ? $oMessage->getHTMLBody() : $oMessage->getTextBody();$message['has_attachments'] = $oMessage->getAttachments()->count() > 0 ? true : false;
                        // $message['subject']         = utf8_decode(str_replace("_", " ", mb_decode_mimeheader($oMessage->subject)));
                       
                        $attachments                = ($message['has_attachments']) ? $oMessage->getAttachments() : [];
                        $mail_attachments           = array();
                        $dir                        =  public_path()."/mail_attatchments";
                        if ( !file_exists($dir) && !is_dir($dir)) {
                            mkdir ($dir, 0744);
                        }
                        // if (!file_exists($target_dir) && !is_dir($target_dir)) {
                        //     //Make Dir  with permissions
                        //     mkdir($target_dir, 0777, true);         
                        // }
                        foreach ($attachments  as $key_2 => $attachment) {
                            $new_attactchent                = array();
                            $new_attactchent['extension']   =  $attachment->getExtension();
                            $new_attactchent['name']        =  $attachment->name;
                            $new_attactchent['mime']        =  $attachment->getMimeType();
                            $new_attactchent['path']        = $attachment->save($path = public_path()."/mail_attatchments/", $filename = null);
                            $mail_attachments[]             = $new_attactchent;
                        }
                        $message['attachments'] = $mail_attachments;
                        $messages[]             = $message;
                    }

                    $paginated_messages['messages'] = array_reverse($messages);
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

    public function sendMail (Request $request){
        // return $request->all();
        
        $data = $request->all();
        $validator = $this->validatorMail($data);
  
        if ($validator->passes()) {
 
            if(isset($request->Files))
            {
            
                Mailgun::send('mailing.mail', $data, function ($message) use ($data,$request){
                    $message->from($data['email_de'],$data['nombre_de']);
                    $message->subject($data['asunto']);
                    // $message->bcc($data['email_de']);
                    $message->to($data['email_para']);
                    
                    for($x = 0; $x < count($request->Files); $x++)
                    {
                        $message->attach($request->Files[$x]->getRealPath(), $request->Files[$x]->getClientOriginalName());
                    }   
                });
                
            }
            else
            {
            
                Mailgun::send('mailing.mail', $data, function ($message) use ($data){
                    $message->from($data['email_de'],$data['nombre_de']);
                    // $message->bcc($data['email_de']);
                    $message->subject($data['asunto']);
                    $message->to($data['email_para']);
                });
            }
            
            
            //Create record of the email sent
            MailingInboxService::createResponse($data);
                    
            return response()->json([
            'error'=>false,
            'message'=>'Mail enviado correctamente',
            ],200);
        }else{
            $errores = $validator->errors()->toArray();
        
            $errores_msg = array();
    
            if (!empty($errores)) {
                foreach ($errores as $key => $error_m) {
                    $errores_msg[] = $error_m[0];
                    break;
                }
            }
    
            return response()->json([
                'error'=>true,
                'message'=> $errores_msg
            ],400);
        }
    }

    public function validatorMail(array $data){
        return Validator::make($data,[
            'email_de'      => 'required|email',
            'nombre_de'     => 'string|max:255',
            'nombre_para'   => 'string|max:255',
            'asunto'        => 'required|string|max:255',
            'contenido'     => 'required',
            'Files'         => 'array',
            'Files.*'       => 'file|max:20480',
        ]);
    }

    public function flagEmail(Request $request){
        // return $request->all();

        
        ini_set('memory_limit', '-1');
        
        $colaborador_id = Auth::user()->id;
        // $colaborador_id = '0e940a0c-c474-3463-bceb-0db0ad1fd42b';
        
        $colaborador = User::find($colaborador_id);
        
        if(isset($colaborador->id)){

            $account =  MailingInboxService::getAccount($colaborador_id);
            
            if(isset($account->password)){
                
                // return $account;
                
                $username = (!is_null($account->alt_email)) ? $account->alt_email : $colaborador->email;

                $account->password = Crypt::decryptString($account->password);


                $client = \Webklex\IMAP\Facades\Client::make([
                    'host'          => $account->host,
                    'port'          => $account->port,
                    'encryption'    => $account->encryption,
                    'validate_cert' => true,
                    'username'      => $username,
                    'password'      => $account->password,
                    'protocol'      => 'imap'
                ]);

                $client->connect();

                $oFolder     =  $client->getFolder('INBOX');
                $message = $oFolder->query()->getMessageByUid($request->UID);
                if($request->seen_flag == 1){
                    $message->unsetFlag('SEEN');
                    $client->expunge();
                }else{
                    $message->setFlag('Seen');    
                }
                
                return response()->json([
                    'error'     => false,
                    'paginator' => $message,
                ],200);
            }
        }
    }

    function utf8convert($mixed, $key = null)
      {
          if (is_array($mixed)) {
              foreach ($mixed as $key => $value) {
                  $mixed[$key] = $this->utf8convert($value, $key); //recursive
              }
          } elseif (is_string($mixed)) {
              $fixed = mb_convert_encoding($mixed, "UTF-8", "UTF-8");
              return $fixed;
          }
          return $mixed;
    }


}

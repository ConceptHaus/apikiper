<?php

namespace App\Http\Controllers\Prospectos;

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

use App\Modelos\User;
use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\DetalleProspecto;
use App\Modelos\Prospecto\EtiquetasProspecto;
use App\Modelos\Prospecto\ArchivosProspectoColaborador;
use App\Modelos\Prospecto\ColaboradorProspecto;

use App\Modelos\Oportunidad\Oportunidad;
use App\Modelos\Oportunidad\DetalleOportunidad;
use App\Modelos\Oportunidad\EtiquetasOportunidad;
use App\Modelos\Oportunidad\ColaboradorOportunidad;
use App\Modelos\Oportunidad\ServicioOportunidad;
use App\Modelos\Oportunidad\ProspectoOportunidad;

use App\Modelos\Empresa\Empresa;
use App\Modelos\Empresa\EmpresaProspecto;
use App\Http\Services\Auth\AuthService;

use App\Evento;
use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\DetalleRecordatorioProspecto;
use App\Modelos\Extras\DetalleEvento;
use App\Modelos\Oportunidad\StatusOportunidad;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\CatStatusProspecto;
use App\Events\Historial;
use App\Events\Event;
use \App\Http\Enums\Permissions;
use App\Imports\ProspectosImport;
use App\Exports\ProspectosReports;
use Excel;

use App\Http\Enums\OldRole;
use App\Http\Services\Users\UserService;
use App\Http\Services\Roles\RolesService;
use App\Modelos\Role;

use DB;
use Mail;
use Mailgun;
use Carbon\Carbon;
class ProspectosController extends Controller
{

    private $userServ;
    private $roleServ;

    public function __construct(){
    }

    public function getChats($method, $url, $data){
            $curl = curl_init();
            switch ($method){
               case "POST":
                  curl_setopt($curl, CURLOPT_POST, 1);
                  if ($data)
                     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                  break;
            //    case "PUT":
            //       curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            //       if ($data)
            //          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
            //       break;
               default:
                  if ($data)
                     $url = sprintf("%s?%s", $url, http_build_query($data));
            }

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
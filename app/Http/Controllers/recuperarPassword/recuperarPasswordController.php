<?php

namespace App\Http\Controllers\Mailing;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Modelos\Mailing\Mailings;
use App\Modelos\Mailing\DetalleMailings;
use App\Modelos\Mailing\ImagesMailings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;


use Mailgun;
use DB;

class recuperarPasswordController extends Controller 
{

    public function mailforgot(Request $request){
        $queryusuario = mysqli_query("SELECT * FROM users WHERE email = '$email'");
        $numregistro = mysqli_num_rows($queryusuario); 

    if ($numregistro == 1)
        {
            $mostrar = mysqli_fetch_array($queryusuario);
        }  else {
         echo "error";
        }
    }
}
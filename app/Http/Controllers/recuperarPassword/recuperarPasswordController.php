<?php

namespace App\Http\Controllers\Mailing;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Modelos\Recuperarpassword\recuperarpassword;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;


use Mailgun;
use DB;

class recuperarpasswordController extends Controller 
{

    public function mailforget(Request $request){   
        $queryusuario = mysqli_query("SELECT * FROM users WHERE email = '$email'");
        $numregistro = mysqli_num_rows($queryusuario); 

        if ($numregistro == 1){
            $mostrar		= mysqli_fetch_array($queryusuario); 
            $enviarpass 	= utf8_decode($mostrar['password']);
            
            $paracorreo 		= $email;
            $titulo				= "Recuperar contraseña";
            $mensaje			= $enviarpass;
            $tucorreo			= "From: xxxx@gmail.com";
            
            if(mail($paracorreo,$titulo,$mensaje,$tucorreo))
            {
                echo "<script> alert('Contraseña enviado');window.location= 'index.html' </script>";
            }else
            {
                echo "<script> alert('Error');window.location= 'index.html' </script>";
            }
        }
        else
            {
                echo "<script> alert('Este correo no existe');window.location= 'index.html' </script>";
            }
    }
}
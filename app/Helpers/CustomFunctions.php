<?php
    //Permissions List
    define("PROSPECTOS_LEER_PROPIOS", "prospectos.read.own");
    define("PROSPECTOS_LEER_TODOS", "prospectos.read.all2");


    if(!function_exists('getAuthenticatedUserPermissions'))
    {
        function getAuthenticatedUserPermissions()
        {
            return json_decode(Auth::user()->role->acciones, true);
        }
    }
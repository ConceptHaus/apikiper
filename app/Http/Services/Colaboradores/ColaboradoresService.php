<?php
namespace App\Http\Services\Colaboradores;
use App\Http\Repositories\Colaboradores\ColaboradoresRep;
use Illuminate\Support\Facades\Validator;

class ColaboradoresService
{
    

    public static function validator(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'apellido'=> 'required|string|max:255',
            'role_id' => 'required|integer',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255'
        ]);
    }

    public static function validatorUpdate(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|string|max:255',
            'apellido'=> 'required|string|max:255',
            'role_id'=> 'required|integer',
            'puesto' => 'required|string|max:255',
            'telefono'=> 'required|string|max:255',
        ]);
    }

    public static function getColaborador($id_colaborador)
    {
        return ColaboradoresRep::getColaborador($id_colaborador);
    }

    public static function getColaboradorExt($id_colaborador)
    {
        return ColaboradoresRep::getColaboradorExt($id_colaborador);
    }

    public static function updateColaborador($colaborador_info, $colaborador,  $colaborador_ext, $auth)
    {
        return ColaboradoresRep::updateColaborador($colaborador_info, $colaborador,  $colaborador_ext, $auth);
    }

    public static function createColaborador($new_colaborador, $auth)
    {
        return ColaboradoresRep::createColaborador($new_colaborador, $auth);
    }
}

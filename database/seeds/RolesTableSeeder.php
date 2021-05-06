<?php

use Illuminate\Database\Seeder;
use App\Modelos\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $new_rol                = new Role();
        $new_rol->nombre        = "Colaborador";
        $new_rol->acciones      = '["prospectos.read.own"]';
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Lider Colaboradores";
        $new_rol->acciones      = '["prospectos.read.all"]';
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Admin Cliente";
        $new_rol->acciones      = '["prospectos.read.all",
                                    "fe.sidebar.newsletter",
                                    "fe.sidebar.ajustes.colaboradores",
                                    "fe.colaboradores.nuevo"
                                    ]';
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Cuentas Kiper";
        $new_rol->acciones      = '["prospectos.read.all",
                                    "fe.sidebar.operaciones.generales",
                                    "fe.sidebar.ajustes.colaboradores",
                                    "fe.colaboradores.nuevo"
                                    ]'; 
        $new_rol->is_visible    = 0;
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Administrador Kiper";
        $new_rol->acciones      = '["prospectos.read.all",
                                    "fe.sidebar.operaciones.generales",
                                    "fe.sidebar.estadisticas",
                                    "fe.sidebar.newsletter",
                                    "fe.sidebar.ajustes.integraciones",
                                    "fe.sidebar.ajustes.colaboradores",
                                    "fe.sidebar.ajustes.servicios",
                                    "fe.sidebar.ajustes.etiquetas",
                                    "fe.sidebar.ajustes.estatus",
                                    "fe.colaboradores.nuevo"
                                    ]';
        $new_rol->is_visible    = 0;
        $new_rol->save();
    }
}

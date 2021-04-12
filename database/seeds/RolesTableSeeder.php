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
        //$new_rol->acciones      = "";
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Lider Colaboradores";
        //$new_rol->acciones      = "";
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Admin Cliente";
        //$new_rol->acciones      = "";
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Cuentas Kiper";
        //$new_rol->acciones      = "";
        $new_rol->save();

        $new_rol                = new Role();
        $new_rol->nombre        = "Administrador Kiper";
        //$new_rol->descripcion   = "";
        $new_rol->save();
    }
}

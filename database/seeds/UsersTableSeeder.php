<?php

use Illuminate\Database\Seeder;
use App\Modelos\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat_status_oportunidad             = new User();
        $cat_status_oportunidad->nombre     = "Usuario Test";
        $cat_status_oportunidad->apellido   = "Admin Kiper ";
        $cat_status_oportunidad->email      = "leslye@kiper.app";
        $cat_status_oportunidad->password   = '$2y$10$xVH1mAebx68O.4GqUbTVleNQ1DA2qiHjx7oZ8xphF0.Gkg.8OaNMC'; //prueba123
        $cat_status_oportunidad->status     = 1;
        $cat_status_oportunidad->role_id    = 5;
        $cat_status_oportunidad->save();

        $cat_status_oportunidad             = new User();
        $cat_status_oportunidad->nombre     = "Roberto Correa";
        $cat_status_oportunidad->apellido   = "Admin Kiper ";
        $cat_status_oportunidad->email      = "hola@kiper.app";
        $cat_status_oportunidad->password   = '$2y$10$xVH1mAebx68O.4GqUbTVleNQ1DA2qiHjx7oZ8xphF0.Gkg.8OaNMC'; //prueba123
        $cat_status_oportunidad->status     = 1;
        $cat_status_oportunidad->role_id    = 5;
        $cat_status_oportunidad->save();
    }
}

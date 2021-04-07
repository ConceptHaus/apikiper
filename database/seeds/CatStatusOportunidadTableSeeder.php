<?php

use Illuminate\Database\Seeder;
use App\Modelos\Oportunidad\CatStatusOportunidad;

class CatStatusOportunidadTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat_status_oportunidad = new CatStatusOportunidad();
        $cat_status_oportunidad->status = "Cotizado";
        $cat_status_oportunidad->color = "#fac219";
        $cat_status_oportunidad->save();

        $cat_status_oportunidad = new CatStatusOportunidad();
        $cat_status_oportunidad->status = "Cerrado";
        $cat_status_oportunidad->color = "#37af9b";
        $cat_status_oportunidad->save();

        $cat_status_oportunidad = new CatStatusOportunidad();
        $cat_status_oportunidad->status = "No Viable";
        $cat_status_oportunidad->color = "#000000";
        $cat_status_oportunidad->save();
    }
}

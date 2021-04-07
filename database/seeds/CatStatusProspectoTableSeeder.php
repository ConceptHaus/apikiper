<?php

use Illuminate\Database\Seeder;
use App\Modelos\Prospecto\CatStatusProspecto;

class CatStatusProspectoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat_status_prospecto = new CatStatusProspecto();
        $cat_status_prospecto->status = "Contactado";
        $cat_status_prospecto->color = "#18c5a9";
        $cat_status_prospecto->save();

        $cat_status_prospecto = new CatStatusProspecto();
        $cat_status_prospecto->status = "No Contactado";
        $cat_status_prospecto->color = "#f39c12";
        $cat_status_prospecto->save();
    }
}

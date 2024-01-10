<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Modelos\Empresa\Empresa;
use App\Modelos\Empresa\EmpresaProspecto;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresa = factory(Empresa::class,10)->create();
        $empresaProspecto = factory(EmpresaProspecto::class,150)->create();
    }
}

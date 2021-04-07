<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Modelos\Prospecto\Prospecto;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();
        $this->call(CatalogoSeeder::class);
        $this->call(CatIndustriaSeeder::class);
        $this->call(CatStatusProspectoTableSeeder::class);
        $this->call(CatStatusOportunidadTableSeeder::class);
        $etiquetas = factory(App\Modelos\Extras\Etiqueta::class,1500)->create();
        $servicios = factory(App\Modelos\Oportunidad\CatServicios::class,150)->create();
        $medios_contacto = factory(App\Modelos\Prospecto\CatMedioContacto::class,5)->create();
        $colaborador = factory(App\Modelos\User::class,1500)->create()
                        ->each(function($u){
                            $u->detalle()->save(factory(App\Modelos\Colaborador\DetalleColaborador::class)->make());
                            $u->foto()->save(factory(App\Modelos\Colaborador\FotoColaborador::class)->make());
                        });
        $prospecto = factory(App\Modelos\Prospecto\Prospecto::class,1500)->create()
                        ->each(function($p){
                            $p->detalle_prospecto()->save(factory(App\Modelos\Prospecto\DetalleProspecto::class)->make());
                            $p->status_prospecto()->save(factory(App\Modelos\Prospecto\StatusProspecto::class)->make());
                        });

        $oportunidad = factory(App\Modelos\Oportunidad\Oportunidad::class,1500)->create()
                        ->each(function($o){
                            $o->detalle_oportunidad()->save(factory(App\Modelos\Oportunidad\DetalleOportunidad::class)->make());
                            $o->status_oportunidad()->save(factory(App\Modelos\Oportunidad\StatusOportunidad::class)->make());
                        });

        $prospecto_oportunidad = factory(App\Modelos\Oportunidad\ProspectoOportunidad::class,1500)->create();

        $colaborador_oportunidad = factory(App\Modelos\Oportunidad\ColaboradorOportunidad::class,1500)->create();
        $colaborador_prospecto = factory(App\Modelos\Prospecto\ColaboradorProspecto::class,1500)->create();
        $archivos_prospecto=factory(App\Modelos\Prospecto\ArchivosProspectoColaborador::class,1500)->create();
        $archivos_oportunidad=factory(App\Modelos\Oportunidad\ArchivosOportunidadColaborador::class,1500)->create();
        $etiquetas_prospecto = factory(App\Modelos\Prospecto\EtiquetasProspecto::class,250)->create();
        $mediocontacto_prospecto = factory(App\Modelos\Prospecto\MedioContactoProspecto::class,1500)->create();
        $etiquetas_oportunidad = factory(App\Modelos\Oportunidad\EtiquetasOportunidad::class,1500)->create();
        $servicios_oportunidad = factory(App\Modelos\Oportunidad\ServicioOportunidad::class,1500)->create();
        $evento_prospecto = factory(App\Modelos\Extras\EventoProspecto::class,1500)->create()
                            ->each(function($e){
                                $e->detalle()->save(factory(App\Modelos\Extras\DetalleEventoProspecto::class)->make());
                            });

        $evento_oportunidad=factory(App\Modelos\Extras\EventoOportunidad::class,1500)->create()
                            ->each(function($e){
                                $e->detalle()->save(factory(App\Modelos\Extras\DetalleEventoOportunidad::class)->make());
                            });

        $recordatorio_prospecto=factory(App\Modelos\Extras\RecordatorioProspecto::class,1500)->create()
                            ->each(function($e){
                                $e->detalle()->save(factory(App\Modelos\Extras\DetalleRecordatorioProspecto::class)->make());
                            });

        $recordatorio_oportunidad=factory(App\Modelos\Extras\RecordatorioOportunidad::class,1500)->create()
                            ->each(function($e){
                                $e->detalle()->save(factory(App\Modelos\Extras\DetalleRecordatorioOportunidad::class)->make());
                            });

        Model::reguard();

    }
}

<?php

use Faker\Generator as Faker;

$factory->define(App\Modelos\Oportunidad\Oportunidad::class, function (Faker $faker) {
    return [
        'id_oportunidad'=>$faker->uuid,
        'nombre_oportunidad'=>$faker->city
    ];
});

$factory->define(App\Modelos\Oportunidad\DetalleOportunidad::class, function (Faker $faker) {
    return [
        'descripcion'=> $faker->realText($maxNbChars = 30),
        'valor'=>$faker->numberBetween($min=80000, $max=660000),
        'meses'=>$faker->randomDigit()
    ];
});

$factory->define(App\Modelos\Oportunidad\StatusOportunidad::class, function (Faker $faker) {
    return [
        'id_cat_status_oportunidad'=>$faker->randomElement([1,2,3])
    ];
});

$factory->define(App\Modelos\Oportunidad\ArchivosOportunidadColaborador::class, function(Faker $faker){
    $colaboradores = App\Modelos\User::pluck('id')->toArray(); 
    $oportunidades = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    return [
        'id_colaborador'=> $faker->randomElement($colaboradores),
        'id_oportunidad'=> $faker->randomElement($oportunidades),
        'nombre'=> $faker->word.'.'.$faker->fileExtension,
        'descripcion'=> $faker->realText($maxNbChars = 30),
        'url'=>$faker->imageUrl($width = 640, $height = 480)
    ];
});

$factory->define(App\Modelos\Oportunidad\EtiquetasOportunidad::class, function(Faker $faker){
    $etiquetas = App\Modelos\Extras\Etiqueta::pluck('id_etiqueta')->toArray();
    $oportunidades = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    return [
        'id_oportunidad'=>$faker->randomElement($oportunidades),
        'id_etiqueta'=>$faker->randomElement($etiquetas)
    ];
});

$factory->define(App\Modelos\Oportunidad\ServicioOportunidad::class, function(Faker $faker){
    $servicios = App\Modelos\Oportunidad\CatServicios::pluck('id_servicio_cat')->toArray();
    $oportunidades = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    return [
        'id_oportunidad'=>$faker->randomElement($oportunidades),
        'id_servicio_cat'=>$faker->randomElement($servicios)
    ];
});


//oportunidad evento
$factory->define(App\Modelos\Extras\EventoOportunidad::class, function(Faker $faker){
    $oportunidad = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    $colaborador = App\Modelos\User::pluck('id')->toArray();
    return [
        'id_oportunidad'=>$faker->randomElement($oportunidad),
        'id_colaborador'=>$faker->randomElement($colaborador)
    ];
});

$factory->define(App\Modelos\Extras\DetalleEventoOportunidad::class,function(Faker $faker){
        return [
            'fecha_evento'=>$faker->date($format='Y-m-d'),
            'hora_evento'=>$faker->time($format = 'H:i:s', $max = 'now'),
            'nota_evento'=>$faker->realText($maxNbChars = 30)
        ];
});

//oportunidad recordatorio
$factory->define(App\Modelos\Extras\RecordatorioOportunidad::class, function(Faker $faker){
    $oportunidad = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    $colaborador = App\Modelos\User::pluck('id')->toArray();
    return [
        'id_oportunidad'=>$faker->randomElement($oportunidad),
        'id_colaborador'=>$faker->randomElement($colaborador)
    ];
});

$factory->define(App\Modelos\Extras\DetalleRecordatorioOportunidad::class,function(Faker $faker){
        return [
            'fecha_recordatorio'=>$faker->date($format='Y-m-d'),
            'hora_recordatorio'=>$faker->time($format = 'H:i:s', $max = 'now'),
            'nota_recordatorio'=>$faker->realText($maxNbChars = 30)
        ];
});


<?php

use Faker\Generator as Faker;

//etiquetas
$factory->define(App\Modelos\Extras\Etiqueta::class, function (Faker $faker) {
    return [
        'nombre'=> $faker->word,
        'descripcion'=>$faker->sentence($nbWords=6)
    ];
});

//servicios
$factory->define(App\Modelos\Oportunidad\CatServicios::class, function (Faker $faker) {
    return [
        'nombre'=> $faker->word,
        'descripcion'=>$faker->sentence($nbWords=6)
    ];
});


//medio contacto

$factory->define(App\Modelos\Prospecto\CatMedioContacto::class, function (Faker $faker) {
    return [
        'nombre'=> $faker->word,
    ];
});
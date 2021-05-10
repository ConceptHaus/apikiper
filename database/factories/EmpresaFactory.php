<?php

use Faker\Generator as Faker;

$factory->define(App\Modelos\Empresa\Empresa::class, function (Faker $faker) {
    $cat_industria = App\Modelos\Extras\CatIndustria::pluck('id_cat_industria')->toArray();
    return [
        'nombre'=>$faker->company,
        'cp'=>$faker->postcode,
        'calle'=>$faker->streetAddress,
        'colonia'=>$faker->cityPrefix,
        'num_ext'=>$faker->buildingNumber,
        'num_int'=>$faker->buildingNumber,
        'pais'=>$faker->country,
        'estado'=>$faker->state,
        'municipio'=>$faker->citySuffix,
        'ciudad'=>$faker->city,
        'telefono'=>$faker->phoneNumber,
        'num_empleados'=>$faker->randomDigit,
        'id_cat_industria'=>$faker->randomElement($cat_industria),
        'web'=>$faker->url,
        'rfc'=>$faker->numberBetween($min = 1000, $max = 9000),
        'razon_social'=>$faker->sentence($nbWords = 3, $variableNbWords = true)
    ];
});


$factory->define(App\Modelos\Empresa\EmpresaProspecto::class, function(Faker $faker){
    $empresa = App\Modelos\Empresa\Empresa::pluck('id_empresa')->toArray();
    $prospecto = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    return [
        'id_empresa'=>$faker->randomElement($empresa),
        'id_prospecto'=>$faker->randomElement($prospecto)
    ];
});
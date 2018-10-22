<?php

use Faker\Generator as Faker;

$factory->define(App\Modelos\User::class, function (Faker $faker) {
    return [
        'id'=>$faker->uuid,
        'nombre'=>$faker->firstNameFemale,
        'apellido'=>$faker->lastName,
        'email'=>$faker->unique()->safeEmail,
        'password'=>bcrypt('prueba123456'),
        'is_admin'=>$faker->randomElement([0,1]),
        'status'=>1
    ];
});

$factory->define(App\Modelos\Colaborador\DetalleColaborador::class, function (Faker $faker) {
    return [
        'puesto'=>$faker->jobTitle,
        'telefono'=>$faker->tollFreePhoneNumber,
        'fecha_nacimiento'=>$faker->date($format = 'Y-m-d', $max = 'now'),
        
    ];
});

$factory->define(App\Modelos\Colaborador\FotoColaborador::class, function (Faker $faker){
    return [
        'url_foto'=>$faker->imageUrl($width=640,$height=480,'people')
    ];
});

$factory->define(App\Modelos\Oportunidad\ColaboradorOportunidad::class,function(Faker $faker){
    $colaboradores = App\Modelos\User::pluck('id')->toArray();
    $oportunidades = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    return [
        'id_colaborador' => $faker->randomElement($colaboradores),
        'id_oportunidad' => $faker->randomElement($oportunidades)
    ];

});

$factory->define(App\Modelos\Prospecto\ColaboradorProspecto::class,function(Faker $faker){
    $colaboradores = App\Modelos\User::pluck('id')->toArray();
    $prospectos = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    return [
        'id_colaborador' => $faker->randomElement($colaboradores),
        'id_prospecto' => $faker->randomElement($prospectos)
    ];

});





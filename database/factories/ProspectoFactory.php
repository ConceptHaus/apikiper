<?php

use Faker\Generator as Faker;

$factory->define(App\Modelos\Prospecto\Prospecto::class, function (Faker $faker) {
    return [
        'id_prospecto'=>$faker->uuid,
        'nombre'=>$faker->firstNameFemale,
        'apellido'=>$faker->lastName,
        'correo'=>$faker->unique()->safeEmail,
        'fuente'=>$faker->randomElement([1,2,3,4])
    ];
});

$factory->define(App\Modelos\Prospecto\DetalleProspecto::class, function (Faker $faker){
    return [
        'puesto'=>$faker->jobTitle,
        'empresa'=>$faker->company,
        'telefono'=>$faker->tollFreePhoneNumber,
        'celular'=>$faker->tollFreePhoneNumber,
        'whatsapp'=>$faker->e164PhoneNumber,
        'nota'=>$faker->sentence,
        'extension'=>$faker->buildingNumber,
    ];
});


$factory->define(App\Modelos\Prospecto\StatusProspecto::class, function (Faker $faker){
    return [
        'id_cat_status_prospecto'=>$faker->randomElement([1,2]),
    ];
});

$factory->define(App\Modelos\Oportunidad\ProspectoOportunidad::class, function (Faker $faker){
    $prospectos = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    $oportunidades = App\Modelos\Oportunidad\Oportunidad::pluck('id_oportunidad')->toArray();
    return [
        'id_prospecto'=>$faker->randomElement($prospectos),
        'id_oportunidad'=>$faker->randomElement($oportunidades)
    ];
});

$factory->define(App\Modelos\Prospecto\ArchivosProspectoColaborador::class, function(Faker $faker){
    $colaboradores = App\Modelos\User::pluck('id')->toArray(); 
    $prospectos = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    return [
        'id_colaborador'=> $faker->randomElement($colaboradores),
        'id_prospecto'=> $faker->randomElement($prospectos),
        'nombre'=> $faker->word.'.'.$faker->fileExtension,
        'desc'=> $faker->realText($maxNbChars = 30),
        'url'=>$faker->imageUrl($width = 640, $height = 480)
    ];
});

$factory->define(App\Modelos\Prospecto\EtiquetasProspecto::class, function(Faker $faker){
    $etiquetas = App\Modelos\Extras\Etiqueta::pluck('id_etiqueta')->toArray();
    $prospectos = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    return [
        'id_prospecto'=>$faker->randomElement($prospectos),
        'id_etiqueta'=>$faker->randomElement($etiquetas)
    ];
});

$factory->define(App\Modelos\Prospecto\MedioContactoProspecto::class, function(Faker $faker){
    $medio_contactos = App\Modelos\Prospecto\CatMedioContacto::pluck('id_mediocontacto_catalogo')->toArray();
    $prospectos = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    return [
        'id_prospecto'=>$faker->randomElement($prospectos),
        'id_mediocontacto_catalogo'=>$faker->randomElement($medio_contactos),
        'descripcion'=>$faker->realText($maxNbChars=20),
        'fecha'=>$faker->date($format = 'Y-m-d', $max = 'now'),
        'hora'=>$faker->time($format = 'H:i:s', $max = 'now')
    ];
});

//prospecto evento
$factory->define(App\Modelos\Extras\EventoProspecto::class, function(Faker $faker){
    $prospecto = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    $colaborador = App\Modelos\User::pluck('id')->toArray();
    return [
        'id_prospecto'=>$faker->randomElement($prospecto),
        'id_colaborador'=>$faker->randomElement($colaborador)
    ];
});

$factory->define(App\Modelos\Extras\DetalleEventoProspecto::class,function(Faker $faker){
        return [
            'fecha_evento'=>$faker->date($format='Y-m-d'),
            'hora_evento'=>$faker->time($format = 'H:i:s', $max = 'now'),
            'nota_evento'=>$faker->realText($maxNbChars = 30)
        ];
});

//prospecto recordatorio
$factory->define(App\Modelos\Extras\RecordatorioProspecto::class, function(Faker $faker){
    $prospecto = App\Modelos\Prospecto\Prospecto::pluck('id_prospecto')->toArray();
    $colaborador = App\Modelos\User::pluck('id')->toArray();
    return [
        'id_prospecto'=>$faker->randomElement($prospecto),
        'id_colaborador'=>$faker->randomElement($colaborador)
    ];
});

$factory->define(App\Modelos\Extras\DetalleRecordatorioProspecto::class,function(Faker $faker){
        return [
            'fecha_recordatorio'=>$faker->date($format='Y-m-d'),
            'hora_recordatorio'=>$faker->time($format = 'H:i:s', $max = 'now'),
            'nota_recordatorio'=>$faker->realText($maxNbChars = 30)
        ];
});
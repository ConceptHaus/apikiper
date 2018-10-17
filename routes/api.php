<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


//User
Route::prefix('/v1/users')->group(function(){

        Route::middleware(['api','cors'])->group(function(){
            Route::post('/login', 'Auth\LoginController@login');
            Route::get('/activate/{token}','Auth\UserController@activateUser');
            Route::post('/password','Auth\UserController@setPassword');
            Route::get('/event', function () {
                broadcast(new App\Events\EventColaborador('Hi there!'));
                return "Event has been sent!";
            });
        });
        
        Route::middleware(['jwt.auth','cors'])->group(function(){
            Route::get('/me','Auth\UserController@getAuthUser');
            Route::post('/logout', 'Auth\LoginController@logout');
        });
});

//Colaboradores
Route::prefix('/v1/colaboradores')->group(function(){
        Route::middleware(['jwt.auth','cors'])->group(function(){
            Route::post('/', 'Colaboradores\ColaboradoresController@registerColaborador');
            Route::get('/', 'Colaboradores\ColaboradoresController@getAllColaboradores');
            Route::get('/{id}','Colaboradores\ColaboradoresController@getOneColaborador');
            Route::put('/{id}','Colaboradores\ColaboradoresController@updateColaborador');
            Route::delete('/{id}','Colaboradores\ColaboradoresController@deleteColaborador');
            
        });  
});

//Prospectos
Route::prefix('/v1/prospectos')->group(function(){
        Route::middleware(['jwt.auth','cors'])->group(function(){
            //CRUD principal
            Route::post('/', 'Prospectos\ProspectosController@registerProspecto');
            Route::get('/','Prospectos\ProspectosController@getAllProspectos');
            Route::get('/{id}','Prospectos\ProspectosController@getOneProspecto');
            Route::put('/{id}','Prospectos\ProspectosController@updateProspecto');
            Route::delete('/{id}','Prospectos\ProspectosController@deleteProspecto');

            //Funcionalidades extras
            Route::get('/{id}/oportunidades','Prospectos\ProspectosController@getOportunidades');
            Route::post('/{id}/oportunidades','Prospectos\ProspectosController@addOportunidades');
            Route::get('/{id}/recordatorios','Prospectos\ProspectosController@getRecordatorios');
            Route::post('/{id}/recordatorios','Prospectos\ProspectosController@addRecordatorios');
            Route::get('/{id}/eventos','Prospectos\ProspectosController@getEventos');
            Route::post('/{id}/eventos','Prospectos\ProspectosController@addEventos');
            Route::get('/{id}/etiquetas','Prospectos\ProspectosController@getEtiquetas');
            Route::post('/{id}/etiquetas','Prospectos\ProspectosController@addEtiquetas');
            Route::get('/{id}/archivos','Prospectos\ProspectosController@getArchivos');
            Route::post('/{id}/archivos','Prospectos\ProspectosController@addArchivos');


            
        });  
});

//Oportunidades
Route::prefix('/v1/oportunidades')->group(function(){
        Route::middleware(['jwt.auth','cors'])->group(function(){
            //CRUD principal
            Route::get('/','Oportunidades\OportunidadesController@getAllOportunidades');
            Route::get('/{id}','Oportunidades\OportunidadesController@getOneOportunidad');
            Route::put('/{id}','Oportunidades\OportunidadesController@updateOportunidad');
            Route::delete('/{id}','Oportunidades\OportunidadesController@deleteOportunidad');

            //Funcionalidades extras
            Route::get('/{id}/etiquetas','Oportunidades\OportunidadesController@getEtiquetas');
            Route::post('/{id}/etiquetas','Oportunidades\OportunidadesController@addEtiquetas');
            Route::get('/{id}/archivos','Oportunidades\OportunidadesController@getArchivos');
            Route::post('/{id}/archivos','Oportunidades\OportunidadesController@addArchivos');
            Route::get('/{id}/eventos','Oportunidades\OportunidadesController@getEventos');
            Route::post('/{id}/eventos','Oportunidades\OportunidadesController@addEventos');
            Route::get('/{id}/recordatorios','Oportunidades\OportunidadesController@getRecordatorios');
            Route::post('/{id}/recordatorios','Oportunidades\OportunidadesController@addRecordatorios');
            
            
        });  
});


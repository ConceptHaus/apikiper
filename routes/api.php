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


//Integrations
Route::prefix('/v1')->group(function(){
    Route::middleware(['api','cors'])->group(function(){
        Route::post('/google','Integraciones\GoogleController@googleApi');
         Route::post('/google/callback','Integraciones\GoogleController@googleApiCallback');
    });
});

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

        Route::middleware(['auth','cors'])->group(function(){
            Route::get('/me','Auth\UserController@getAuthUser');
            Route::get('/me/oportunidades','Auth\UserController@oportunidades');
            Route::get('/me/prospectos','Auth\UserController@oportunidades');
            Route::put('/me', 'Auth\UserController@updateMe');
            Route::post('/logout', 'Auth\LoginController@logout');

        });
});

//Colaboradores
Route::prefix('/v1/colaboradores')->group(function(){
        Route::middleware(['cors'])->group(function(){
            Route::post('/', 'Colaboradores\ColaboradoresController@registerColaborador');
            Route::get('/', 'Colaboradores\ColaboradoresController@getAllColaboradores');
            Route::get('/{id}','Colaboradores\ColaboradoresController@getOneColaborador');
            Route::put('/{id}','Colaboradores\ColaboradoresController@updateColaborador');
            Route::delete('/','Colaboradores\ColaboradoresController@deleteColaborador');

        });
});

//Prospectos
Route::prefix('/v1/prospectos')->group(function(){
        Route::middleware(['cors'])->group(function(){
            //CRUD principal
            Route::post('/', 'Prospectos\ProspectosController@registerProspecto');
            Route::get('/','Prospectos\ProspectosController@getAllProspectos');
            Route::get('/{id}','Prospectos\ProspectosController@getOneProspecto');
            Route::get('/status/no-contactados', 'Prospectos\ProspectosController@getProspectosNoContactado');
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
        Route::middleware(['cors'])->group(function(){
            //CRUD principal
            Route::get('/','Oportunidades\OportunidadesController@getAllOportunidades');
            Route::get('/status/{status}','Oportunidades\OportunidadesController@getAllOportunidadesStatus');
            Route::get('/{id}','Oportunidades\OportunidadesController@getOneOportunidad');
            Route::put('/{id}','Oportunidades\OportunidadesController@updateOneOportunidad');
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

            Route::get('/{id}/status','Oportunidades\OportunidadesController@getStatus');
            Route::post('/{id}/status','Oportunidades\OportunidadesController@updateStatus');

            Route::get('/{id}/servicios','Oportunidades\OportunidadesController@getServicios');
            Route::post('/{id}/servicios','Oportunidades\OportunidadesController@addServicios');
            Route::delete('/{id}/servicios','Oportunidades\OportunidadesController@deleteServicios');

            Route::post('/{id}/valor','Oportunidades\OportunidadesController@addValor');

        });
});


//DataViews

Route::prefix('/v1/generales')->group(function(){
    Route::middleware(['cors'])->group(function(){
        Route::get('/dashboard','DataViews\DataViewsController@dashboard');
        Route::get('/dashboard/semanal','DataViews\DataViewsController@dashboardSemanal');
        Route::get('/dashboard/mensual','DataViews\DataViewsController@dashboardMensual');
        Route::get('/dashboard/anual','DataViews\DataViewsController@dashboardAnual');
        Route::get('/prospectos','DataViews\DataViewsController@prospectos');
        Route::get('/prospectos/{status}','DataViews\DataViewsController@prospectosstatus');
        Route::get('/mis-oportunidades','DataViews\DataViewsController@misOportunidades');
        Route::get('oportunidades/{id}','DataViews\DataViewsController@oportunidadesByUser');
        Route::get('mis-oportunidades/{status}','DataViews\DataViewsController@mis_oportunidades_status');
        Route::get('/estadisticas/oportunidades','DataViews\DataViewsController@estadisticas_oportunidad');
        Route::get('/estadisticas/colaboradores','DataViews\DataViewsController@estadisticas_colaborador');
        Route::get('/estadisticas/finanzas','DataViews\DataViewsController@estadisticas_finanzas');
        Route::get('/estadisticas/finanzas/semanal','DataViews\DataViewsController@estadisticas_finanzas_semanal');
        Route::get('/estadisticas/finanzas/mensual','DataViews\DataViewsController@estadisticas_finanzas_mensual');
        Route::get('/estadisticas/finanzas/anual','DataViews\DataViewsController@estadisticas_finanzas_anual');
        Route::get('/etiquetas','DataViews\DataViewsController@etiquetas');
        Route::get('/status','DataViews\DataViewsController@status_oportunidades');
        Route::get('/servicios','DataViews\DataViewsController@servicios');
        Route::get('/colaboradores','DataViews\DataViewsController@colaboradores');
        Route::get('/medios-contacto', 'DataViews\DataViewsController@getMedioContacto');

        //POST
        Route::post('/etiquetas','DataViews\DataViewsController@addEtiquetas');
        Route::post('/servicios','DataViews\DataViewsController@addServicios');
        Route::post('/mail','DataViews\DataViewsController@sendMail');

        //PUT
        Route::put('/etiquetas','DataViews\DataViewsController@updateEtiquetas');
        Route::put('/servicios','DataViews\DataViewsController@updateServicios');
        Route::put('/status','DataViews\DataViewsController@updateStatus');

        //DELETE
        Route::delete('/etiquetas/{id}','DataViews\DataViewsController@deleteEtiquetas');
        Route::delete('/servicios/{id}','DataViews\DataViewsController@deleteServicios');

    });
});

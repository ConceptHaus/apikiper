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
        Route::get('/template',function(){return view('mailing.template_one');});
        Route::get('/download/prospectos/{role_id}/{rol}/{id_user}/{correos}/{nombre}/{telefono}/{status}/{grupo}/{etiquetas}/{fechaInicio}/{fechaFin}/{colaboradores}','Prospectos\ProspectosController@downloadProspectos');
    });
});

//User
Route::prefix('/v1/users')->group(function(){
        Route::middleware(['api','cors'])->group(function(){
            Route::post('/login', 'Auth\LoginController@login');
            Route::get('/activate/{token}','Auth\UserController@activateUser');
            Route::post('/password','Auth\UserController@setPassword');
            Route::get('/onboarding','Auth\UserController@onBoarding');
            Route::post('/create','Auth\UserController@createUser');
        });

        Route::middleware(['auth','cors'])->group(function(){
            Route::get('/me','Auth\UserController@getAuthUser');
            Route::get('/me/oportunidades','Auth\UserController@oportunidades');
            Route::get('/me/prospectos','Auth\UserController@oportunidades');
            Route::put('/me', 'Auth\UserController@updateMe');
            Route::post('/logout', 'Auth\LoginController@logout');
            Route::put('/change-password', 'Auth\UserController@changePassword');

        });
});

//Colaboradores
Route::prefix('/v1/colaboradores')->group(function(){
        Route::middleware(['auth','cors'])->group(function(){
            Route::post('/', 'Colaboradores\ColaboradoresController@registerColaborador');
            Route::get('/', 'Colaboradores\ColaboradoresController@getAllColaboradores');
            Route::get('/{id}','Colaboradores\ColaboradoresController@getOneColaborador');
            Route::get('/etiquetas/{id_etiqueta}','Forms\FormsController@assigment_colaborador');
            Route::put('/{id}','Colaboradores\ColaboradoresController@updateColaborador');
            Route::get('/{id}/recordatorios','Colaboradores\ColaboradoresController@getRecordatoriosColaborador');
            Route::post('/status/{status}','Colaboradores\ColaboradoresController@changeRol');          
            Route::post('/recordatorio','Colaboradores\ColaboradoresController@addRecordatorio');            
            Route::post('/delete','Colaboradores\ColaboradoresController@deleteColaborador');
            Route::post('/foto/{id}', 'Colaboradores\ColaboradoresController@addFoto');
            Route::delete('/foto/{id}', 'Colaboradores\ColaboradoresController@deleteFoto');


        });
});

//Prospectos
Route::prefix('/v1/prospectos')->group(function(){
        Route::middleware(['auth','cors'])->group(function(){
            //CRUD principal
            Route::post('/', 'Prospectos\ProspectosController@registerProspecto');
            Route::get('/','Prospectos\ProspectosController@getAllProspectos');
            Route::get('/status','Prospectos\ProspectosController@getAllProspectosPorStatus');
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
            Route::delete('/etiquetas/{etiqueta}', 'Prospectos\ProspectosController@deleteEtiquetas');

            Route::get('/{id}/archivos','Prospectos\ProspectosController@getArchivos');
            Route::post('/{id}/archivos','Prospectos\ProspectosController@addArchivos');

            Route::delete('{id_colaborador}/archivos/{id}','Prospectos\ProspectosController@deleteArchivos');

            Route::get('/{id}/mailing','Prospectos\ProspectosController@sendMailing');

            Route::post('/foto/{id}', 'Prospectos\ProspectosController@addFoto');
            Route::delete('/foto/{id}', 'Prospectos\ProspectosController@deleteFoto');

            Route::get('/status/all','Prospectos\ProspectosController@getStatus');
            
            //import
            Route::post('/import/bulk','Prospectos\ProspectosController@importProspectos');
        });
});

//Empresas
Route::prefix('/v1/empresas')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        //CRUD principal
        Route::post('/', 'Empresas\EmpresaController@registerCompany');
        Route::get('/', 'Empresas\EmpresaController@getAllCompany');
        Route::get('/{id}', 'Empresas\EmpresaController@getOneCompany');
        Route::post('/{id}', 'Empresas\EmpresaController@updateCompany');
        Route::delete('/{id}', 'Empresas\EmpresaController@deleteCompany');
        Route::delete('/prospectos/{id}', 'Empresas\EmpresaController@deleteCompanyProspect');
    });
});

//Oportunidades
Route::prefix('/v1/oportunidades')->group(function(){
        Route::middleware(['auth','cors'])->group(function(){
            //CRUD principal
            Route::get('/','Oportunidades\OportunidadesController@getAllOportunidades');
            Route::get('/status/{status}','Oportunidades\OportunidadesController@getAllOportunidadesStatus');
            Route::get('/{id}','Oportunidades\OportunidadesController@getOneOportunidad');
            Route::put('/{id}','Oportunidades\OportunidadesController@updateOneOportunidad');
            Route::delete('/{id}','Oportunidades\OportunidadesController@deleteOportunidad');

            //Funcionalidades extras
            Route::get('/{id}/etiquetas','Oportunidades\OportunidadesController@getEtiquetas');
            Route::post('/{id}/etiquetas','Oportunidades\OportunidadesController@addEtiquetas');
            Route::delete('/etiquetas/{etiqueta}', 'Oportunidades\OportunidadesController@deleteEtiquetas');

            Route::get('/{id}/archivos','Oportunidades\OportunidadesController@getArchivos');
            Route::post('/{id}/archivos','Oportunidades\OportunidadesController@addArchivos');
            Route::delete('/{oportunidad}/archivos/{id}','Oportunidades\OportunidadesController@deleteArchivos');

            Route::get('/{id}/eventos','Oportunidades\OportunidadesController@getEventos');
            Route::post('/{id}/eventos','Oportunidades\OportunidadesController@addEventos');

            Route::get('/{id}/recordatorios','Oportunidades\OportunidadesController@getRecordatorios');
            Route::post('/{id}/recordatorios','Oportunidades\OportunidadesController@addRecordatorios');

            Route::get('/{id}/status','Oportunidades\OportunidadesController@getStatus');
            Route::post('/{id}/status','Oportunidades\OportunidadesController@updateStatus');

            Route::get('/{id}/servicios','Oportunidades\OportunidadesController@getServicios');
            Route::post('/{id}/servicios','Oportunidades\OportunidadesController@addServicios');
            Route::delete('/{id}/servicios','Oportunidades\OportunidadesController@deleteServicios');

            Route::put('/{id}/valor','Oportunidades\OportunidadesController@addValor');

        });
});


//DataViews

Route::prefix('/v1/generales')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::get('/industrias', 'DataViews\DataViewsController@getIndustrias');
        Route::get('/correos', 'Prospectos\ProspectosListController@findProspectosCorreos');
        Route::get('/prospectosNombres', 'Prospectos\ProspectosListController@findProspectosNombres');
        Route::get('/prospectosTelefono', 'Prospectos\ProspectosListController@findProspectosTelefono');
        Route::post('/table', 'Prospectos\ProspectosListController@findProspectos')->middleware('auth:api');
        Route::get('/count', 'Prospectos\ProspectosListController@findCountProspectos');
        Route::get('/countNT', 'Prospectos\ProspectosListController@findCountProspectosNotContacted');
        Route::get('/prospectosFuente', 'Prospectos\ProspectosListController@findProspectosFuentes');
        Route::get('/prospectosStatus', 'Prospectos\ProspectosListController@findProspectosStatus');
        Route::get('/prospectosColaborador', 'Prospectos\ProspectosListController@findProspectosColaborador');
        Route::get('/prospectosEtiquetas', 'Prospectos\ProspectosListController@findProspectosEtiquetas');
        Route::get('/prospectosPrueba/{status}','Prospectos\ProspectosListNotContactedController@findProspectosNotContacted');
        
        Route::get('/dashboard','DataViews\DataViewsController@dashboard');
        Route::get('/dashboard/semanal','DataViews\DataViewsController@dashboardSemanal');
        Route::get('/dashboard/mensual','DataViews\DataViewsController@dashboardMensual');
        Route::get('/dashboard/anual','DataViews\DataViewsController@dashboardAnual');
        Route::get('/dashboard/{inicio}/{fin}', 'DataViews\DataViewsController@dashboardPorFecha');
        Route::get('/prospectos','DataViews\DataViewsController@prospectos');
        Route::get('/prospectos/{status}','DataViews\DataViewsController@prospectosstatus');
        Route::get('/prospectos-colaborador','DataViews\DataViewsController@prospectosColaborador');
        Route::get('/prospectos-colaborador/{inicio}/{fin}','DataViews\DataViewsController@prospectosColaborador_por_fecha');
        Route::get('/mis-oportunidades','DataViews\DataViewsController@misOportunidades');
        Route::get('oportunidades/{id}','DataViews\DataViewsController@oportunidadesByUser');
        Route::get('mis-oportunidades/{status}','DataViews\DataViewsController@mis_oportunidades_status');
        Route::get('mis-oportunidades/status','DataViews\DataViewsController@mis_oportunidades_status_todos');
        Route::get('/estadisticas/oportunidades','DataViews\DataViewsController@estadisticas_oportunidad');
        Route::get('/estadisticas/oportunidades/{inicio}/{fin}','DataViews\DataViewsController@estadisticas_oportunidad_por_fecha');        
        Route::get('/estadisticas/oportunidades/me','Estadisticas\EstadisticasController@estadisticas_oportunidad_personal');
        Route::get('/estadisticas/oportunidades/me/{inicio}/{fin}','Estadisticas\EstadisticasController@estadisticas_oportunidad_personal_por_fecha');        
        Route::get('/estadisticas/colaboradores','DataViews\DataViewsController@estadisticas_colaborador');
        Route::get('/estadisticas/colaboradores/{inicio}/{fin}','DataViews\DataViewsController@estadisticas_colaborador_por_fecha');        
        Route::get('/estadisticas/finanzas','DataViews\DataViewsController@estadisticas_finanzas');
        Route::get('/estadisticas/finanzas/{inicio}/{fin}', 'DataViews\DataViewsController@estadisticas_finanzas_por_fecha');
        Route::get('/estadisticas/finanzas/semanal','DataViews\DataViewsController@estadisticas_finanzas_semanal');
        Route::get('/estadisticas/finanzas/mensual','DataViews\DataViewsController@estadisticas_finanzas_mensual');
        Route::get('/estadisticas/finanzas/anual','DataViews\DataViewsController@estadisticas_finanzas_anual');
        Route::get('/etiquetas','DataViews\DataViewsController@etiquetas');
        Route::get('/status','DataViews\DataViewsController@status_oportunidades');
        Route::get('/servicios','DataViews\DataViewsController@servicios');
        Route::get('/servicios/ajustes','DataViews\DataViewsController@serviciosAjustes');
        Route::get('/colaboradores','DataViews\DataViewsController@colaboradores');
        Route::get('/medios-contacto', 'DataViews\DataViewsController@getAllMedioContacto');
        Route::get('/medios-contacto/{id}', 'DataViews\DataViewsController@getMedioContacto');
        Route::get('/medios-contacto-oportunidad/{id}', 'DataViews\DataViewsController@getMedioContactoOportunidad');
        Route::get('/fuentes','DataViews\DataViewsController@getFuentes');
        Route::get('/etiquetas/ajustes', 'DataViews\DataViewsController@getEtiquetasAjustes');

        //POST
        Route::post('/estadisticas/oportunidades','DataViews\DataViewsController@estadisticas_oportunidad_grafica');
        Route::post('/etiquetas','DataViews\DataViewsController@addEtiquetas');
        Route::post('/servicios','DataViews\DataViewsController@addServicios');
        Route::post('/mail','DataViews\DataViewsController@sendMail');
        Route::post('/status-prospecto/{id}', 'DataViews\DataViewsController@cambioStatusProspecto');
        Route::post('/medios-contacto', 'DataViews\DataViewsController@addMedioContactoProspecto');
        Route::post('/medios-contacto-oportunidad', 'DataViews\DataViewsController@addMedioContactoOportunidad');

        //PUT
        Route::put('/etiquetas','DataViews\DataViewsController@updateEtiquetas');
        Route::put('/servicios','DataViews\DataViewsController@updateServicios');
// Route::put('/status','DataViews\DataViewsController@updateStatus');
        Route::put('/prospectos/colaborador','DataViews\DataViewsController@updateColaborador');

        //DELETE
        Route::delete('/etiquetas/{id}','DataViews\DataViewsController@deleteEtiquetas');
        Route::delete('/servicios/{id}','DataViews\DataViewsController@deleteServicios');

    });


});

Route::prefix('/v1/forms')->group(function(){
    Route::middleware(['api','cors','guest'])->group(function(){
        Route::post('/register','Forms\FormsController@registerProspecto');
        Route::post('/calls','Forms\FormsController@registerProspectoCalls');
    });
    
    Route::middleware(['auth','cors'])->group(function(){
        Route::post('/new','Forms\FormsController@addNew');
        Route::get('/','Forms\FormsController@getAll');
        Route::get('/{id}','Forms\FormsController@getOne');
        Route::put('/update','Forms\FormsController@updateForm');
        Route::delete('/{id}','Forms\FormsController@deleteForm');
    });

    

});

Route::prefix('/v1/mailing')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::post('/new','Mailing\MailingController@addNew');
        Route::post('/remitentes','Mailing\MailingController@checkRemitentes');
        Route::get('/','Mailing\MailingController@getAll');
        Route::get('/{id}','Mailing\MailingController@getOne');
        Route::put('/update','Mailing\MailingController@updateMailing');
        Route::delete('/{id}','Mailing\MailingController@deleteMailing');
    });
});


Route::prefix('/v1/roles')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::get('/','Roles\RolesController@getAll');
    });
});

Route::prefix('/v1/funnel')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::get('/stages','Funnel\FunnelController@getFunnelStages');
        Route::post('/createStage','Funnel\FunnelController@createFunnelStage');
        Route::get('/mis-oportunidades','Funnel\FunnelController@getMisOportunidades');
        Route::post('/update-status-oportunidad','Funnel\FunnelController@updateOportunidadStatus');
        Route::post('/{colaborador_id}','Funnel\FunnelController@getColaboradorOportunidades');
    });
});

Route::prefix('/v1/status_oportunidades')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::delete('/{id}','Funnel\FunnelController@deleteStatus');
        Route::put('/updateStatus','Funnel\FunnelController@updateStatus');
        Route::get('/','Funnel\FunnelController@getCatStatusOportunidades');
        Route::get('/get_max_count','Funnel\FunnelController@getMaxEstatusOportunidadMaxCount');
        Route::post('/update-status-oportunidad-visibles','Funnel\FunnelController@updateOportunidadStatusVisibles');
    });
});

Route::prefix('/v1/notifcations')->group(function(){
    Route::middleware(['auth','cors'])->group(function(){
        Route::get('/countNotifications','Notifications\NotificationsController@countNotifications');
        Route::post('/updateNotification','Notifications\NotificationsController@updateStatusNotification');
        
        // Oportunidades
        Route::get('/oportunidades','Notifications\NotificationsController@getOportunidadesToSendNotifications');
        Route::get('/oportunidades-to-be-escalated','Notifications\NotificationsController@getOportunidadesToEscalateForAdmin');
        Route::get('/getOportunidades','Notifications\NotificationsController@getOportunidadesNotifications');
        // Prospectos
        Route::get('/prospectos','Notifications\NotificationsController@getProspectosToSendNotifications');
        Route::get('/prospectos-to-be-escalated','Notifications\NotificationsController@getProspectosToEscalateForAdmin');
        Route::get('/getProspectos','Notifications\NotificationsController@getProspectosNotifications');
        Route::get('/getCountProspectos','Notifications\NotificationsController@getCountProspectosNotifications');
        Route::get('/getCountOportunidades','Notifications\NotificationsController@getCountOportunidadesNotifications');

        // Admin Settings
        Route::post('/postSettingNotificationAdmin','SettingsUserNotifications\SettingsUserNotificationsController@postSettingNotificationAdmin');
        Route::get('/getSettingNotificationAdministrador','SettingsUserNotifications\SettingsUserNotificationsController@getSettingNotificationAdministrador');

        // Personal Settings
        Route::post('/postSettingNotificationColaborador','SettingsUserNotifications\SettingsUserNotificationsController@postSettingNotificationColaborador');
        Route::get('/getSettingNotificationColaborador','SettingsUserNotifications\SettingsUserNotificationsController@getSettingNotificationColaborador');
        
    });
});
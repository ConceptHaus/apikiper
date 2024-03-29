<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyReport;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function(){
    return response()->json(['Error'=>'Nothing here. ⚠️'],200);
});
Route::get('/weekly_report', function(){
   $attach = [public_path('reports/Reporte_Avenue_Polanco.xlsx'),public_path('reports/Reporte_Avenue_Napoles.xlsx')];
    // Mail::to(['recepcion@avenuepolanco.mx','fvazquez@residencialavenue.mx'])
    //         ->cc(['sergio@concepthaus.mx',
    //               'lolita@concepthaus.mx',
    //               'mfasja@gfa.com.mx',
    //               'cgorshtein@gfa.com.mx',
    //               'ngorshtein@gfa.com.mx',
    //               'hhidalgo@gfa.com.mx'])

    //         ->send(new WeeklyReport($attach));
    try {

        return response()->json("Email Sent!");
    } catch (\Exception $e) {
        return response()->json($e->getMessage());
    }
});
// Route::get('/{desarrollo}', function($desarrollo){
//     $filter_prospectos = [];
//     $prospectos = App\Modelos\Prospecto\Prospecto::with('detalle_prospecto')
//                     //->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
//                     ->leftjoin('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
//                     ->leftjoin('users','users.id','colaborador_prospecto.id_colaborador')
//                     ->select('*','users.nombre as nombre_colaborador','users.apellido as apellido_colaborador','prospectos.nombre','prospectos.apellido')->get();
    
//     foreach($prospectos as $prospecto){
//         if($prospecto->email == 'jlvaca@avenuepolanco.mx' && $desarrollo == 'napoles'){
//             $etiqueta_prospecto = New App\Modelos\Prospecto\EtiquetasProspecto;
//             $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
//             $etiqueta_prospecto->id_etiqueta = 64;
//             $etiqueta_prospecto->save();

//             array_push($filter_prospectos,$etiqueta_prospecto);
            
//         }
//         if($prospecto->email == 'gcampuzano@residencialavenue.mx' && $desarrollo == 'napoles'){
//             $etiqueta_prospecto = New App\Modelos\Prospecto\EtiquetasProspecto;
//             $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
//             $etiqueta_prospecto->id_etiqueta = 64;
//             $etiqueta_prospecto->save();

//             array_push($filter_prospectos,$etiqueta_prospecto);
            
//         }
//         if($prospecto->email == 'belora@avenuepolanco.mx' && $desarrollo == 'polanco'){
//             $etiqueta_prospecto = New App\Modelos\Prospecto\EtiquetasProspecto;
//             $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
//             $etiqueta_prospecto->id_etiqueta = 56;
//             $etiqueta_prospecto->save();

//             array_push($filter_prospectos,$etiqueta_prospecto);
            
//         }
//         if($prospecto->email == 'mvelazquez@residencialavenue.mx' && $desarrollo == 'polanco'){
//             $etiqueta_prospecto = New App\Modelos\Prospecto\EtiquetasProspecto;
//             $etiqueta_prospecto->id_prospecto = $prospecto->id_prospecto;
//             $etiqueta_prospecto->id_etiqueta = 56;
//             $etiqueta_prospecto->save();

//             array_push($filter_prospectos,$etiqueta_prospecto);
            
//         }

        
//     }
    
//     return response()->json($filter_prospectos,200);
// });


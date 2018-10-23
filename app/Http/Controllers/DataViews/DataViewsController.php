<?php 

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\User;

use DB;
use Mail;

class DataViewsController extends Controller
{
    public function dashboard(){
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial

        $oportuniades_cerradas = DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $colaboradores = DB::table('users')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad',function($join){
                                    $join->on('colaborador_oportunidad.id_oportunidad','=','status_oportunidad.id_oportunidad')
                                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2);
                                })
                                ->join('detalle_oportunidad',function($join){
                                    $join->on('colaborador_oportunidad.id_oportunidad','=','detalle_oportunidad.id_oportunidad');
                                    
                                })->orderBy('detalle_oportunidad.valor','desc')->limit(5)->get();
                                

        $origen_prospecto = DB::table('prospectos')
                                ->select(DB::raw('count(*) as fuente_count, fuente'))->groupBy('fuente')->get();

        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto','=',1)->count();
        
                                
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>$oportuniades_cerradas,
                'oportunidades_cotizadas'=>$oportunidades_cotizadas,
                'prospectos_sin_contactar'=>$prospectos_sin_contactar,
                'colaboradores'=>$colaboradores,
                'ingresos'=>'',
                'origen_prospecto'=>$origen_prospecto
            ]
        ]);

    }

    public function prospectos(){
        $total_prospectos = Prospecto::all()->count();
        $nocontactados_prospectos = DB::table('prospectos')
                                    ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                    ->where('status_prospecto.id_cat_status_prospecto','=',1)->count();
        $prospectos_fuente = DB::table('prospectos')
                                    ->select(DB::raw('count(*) as fuente_count, fuente'))->groupBy('fuente')->get();
        
        $prospectos_t= DB::table('prospectos')
                            ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                            
                            ->select('prospectos.id_prospecto',
                                    'prospectos.nombre',
                                    'prospectos.apellido',
                                    'prospectos.correo',
                                    'detalle_prospecto.telefono',
                                    'prospectos.fuente',
                                    'prospectos.created_at')->get();
        
        $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('status_prospecto.status')->get();

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'prospectos'=>$prospectos,
                'prospectos_total'=>$total_prospectos,
                'prospectos_nocontactados'=> $nocontactados_prospectos,
                'prospectos_fuente'=> $prospectos_fuente
            ]
        ]);
    }

    public function mis_oportunidades($id){
        $oportunidades_total = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)->count();

        $oportunidades_cotizadas = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $oportunidades_cerradas = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_no_viables = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)->count();
        
        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','prospectos.fuente','oportunidades.created_at')
                            ->get();
        

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'total'=>$oportunidades_total,
                'cotizadas'=>$oportunidades_cotizadas,
                'cerradas'=>$oportunidades_cerradas,
                'no_viables'=>$oportunidades_no_viables,
                'oportunidades'=>$oportunidades
            ]
        ]);
    }

    public function mis_oportunidades_status($id, $status){
        
        $nombre_status = DB::table('cat_status_oportunidad')
                            ->select('cat_status_oportunidad.status')
                            ->where('cat_status_oportunidad.id_cat_status_oportunidad','=',intval($status))
                            ->get();

        $total = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',intval($status))->count();

        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->select(DB::raw('count(*) as fuente_count, prospectos.fuente'))->groupBy('prospectos.fuente')->get();

        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','prospectos.fuente','oportunidades.created_at')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',intval($status))
                            ->get();

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'status'=>$nombre_status,
                'total'=>$total,
                'fuentes'=>$fuentes,
                'oportunidades'=> $oportunidades
                
            ]
        ]);
    }

    public function estadisticas_oportunidad(){
        $oportunidades_cotizadas = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $oportunidades_cerradas = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_no_viables = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)->count();
        
        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->select(DB::raw('count(*) as fuente_count, prospectos.fuente'))->groupBy('prospectos.fuente')->get();
        
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'cotizadas'=>$oportunidades_cotizadas,
                'cerradas'=>$oportunidades_cerradas,
                'no_viables'=>$oportunidades_no_viables,
                'fuentes'=>$fuentes
            ]
        ]);

    }

    public function estadisticas_colaborador(){
        $users_ventas = DB::table('users')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('oportunidades','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->select('users.id','users.email',DB::raw("SUM(detalle_oportunidad.valor) as valor_total"))->groupBy('users.email')->orderBy('valor_total','desc')->get();

        $top_3 = '';
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'ventas'=>$users_ventas,
                'top_3'=>$top_3
            ]
        ]);
                    
    }
}
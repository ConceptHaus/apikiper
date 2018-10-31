<?php 

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;


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
                                ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                                ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->select('users.nombre','users.apellido','detalle_colaborador.puesto','fotos_colaboradores.url_foto',DB::raw('count(*) as oportunidades_cerradas, users.id'))
                                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                                ->groupBy('users.id')
                                ->orderBy('oportunidades_cerradas','desc')->limit(5)->get();
                                

        $origen_prospecto = DB::table('prospectos')
                                ->select(DB::raw('count(*) as fuente_count, fuente'))->groupBy('fuente')->get();

        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto','=',1)->count();
        
        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');
                                
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen_prospecto
            ]
            ],200);

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
            ],200);
    }
    public function prospectosstatus($status){
        $prospectos = DB::table('prospectos')
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','detalle_prospecto.telefono','prospectos.created_at','prospectos.fuente','cat_status_prospecto.status')
                        ->get();

        
        

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>$prospectos
            ],200);
    }
    public function oportunidadesByUser($id){
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
            ],200);
    }

    public function misOportunidades(){

        $id = $this->guard()->user()->id;


        $oportunidades_total = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id)->count();

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
                'total'=>[
                    'valor'=>$oportunidades_total,
                    'porcentaje'=>100,
                    'color'=>'#4646B9'

                ],
                'cotizadas'=>[
                    'valor'=>$oportunidades_cotizadas,
                    'porcentaje'=>intval(round($oportunidades_cotizadas*100/$oportunidades_total)),
                    'color'=>$this->colorsOportunidades(1)
                    
                ],
                'cerradas'=>[
                    'valor'=>$oportunidades_cerradas,
                    'porcentaje'=>intval(round($oportunidades_cerradas*100/$oportunidades_total)),
                    'color'=>$this->colorsOportunidades(2)
     
                ],
                'no_viables'=>[
                    'valor'=>$oportunidades_no_viables,
                    'porcentaje'=>intval(round($oportunidades_no_viables*100/$oportunidades_total,PHP_ROUND_HALF_DOWN)),
                    'color'=>$this->colorsOportunidades(3)
                    
                ],
                'oportunidades'=>$oportunidades
            ]
            ],200);
    }


    public function mis_oportunidades_status($status){
        
        $id = $this->guard()->user()->id;


        $nombre_status = DB::table('cat_status_oportunidad')
                            ->select('cat_status_oportunidad.status')
                            ->where('cat_status_oportunidad.id_cat_status_oportunidad','=',intval($status))
                            ->first();
        $total_general = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id)->count();
        $total = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',intval($status))->count();

        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad',$status)
                            ->select(DB::raw('count(*) as fuente_count, prospectos.fuente'))->groupBy('prospectos.fuente')
                            ->get();

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
                'status'=>$nombre_status->status,
                'total'=>[
                    'valor'=>$total,
                    'porcentaje'=>$total*100/$total_general,
                    'color'=>$this->colorsOportunidades($status)
                ],
                'fuentes'=>$fuentes,
                'oportunidades'=> $oportunidades
                
            ]
            ],200);
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
            ],200);

    }

    public function estadisticas_colaborador(){
        $users_ventas = DB::table('users')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('oportunidades','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->select('users.id','users.email',DB::raw("SUM(detalle_oportunidad.valor) as valor_total"))
                        ->where('status_oportunidad.id_cat_status_oportunidad',2)
                        ->groupBy('users.email')->orderBy('valor_total','desc')->get();

        $top_3 = DB::table('users')
                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                ->select('users.id','users.nombre','users.apellido',DB::raw('count(*) as cerradas, users.email'))
                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                ->groupBy('users.email')->orderBy('cerradas','desc')->limit(3)->get();

        
        $colaboradores =  User::with('oportunidad.oportunidad.status_oportunidad')->get();
        
        //DB::table('users')
        //                 ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
        //                 ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
        //                 ->select('users.*')
        //                 ->orderBy('users.email','desc')
        //                 ->get();

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'ventas'=>$users_ventas,
                'top_3'=>$top_3,
                'colaboradores'=>$colaboradores
            ]
            ],200);
                    
    }

    public function estadisticas_finanzas(){
        $total_cotizado = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)
                            ->sum('detalle_oportunidad.valor');
                            
        $total_cerrador = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                            ->sum('detalle_oportunidad.valor');

        $total_noviable = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)
                            ->sum('detalle_oportunidad.valor');

        $top_3 = DB::table('users')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                    ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                    ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                    ->select('users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
                    ->groupBy('users.id')
                    ->orderBy('total_ingresos','desc')
                    ->limit(3)
                    ->get();

        $fuentes = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->select(DB::raw('SUM(detalle_oportunidad.valor) as valor_cerrado'),'prospectos.fuente')->groupBy('prospectos.fuente')
                    ->get();


        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'total_cotizado'=>number_format($total_cotizado,2),
                'total_cerrador'=>number_format($total_cerrador,2),
                'total_noviable'=>number_format($total_noviable,2),
                'top_3'=>$top_3,
                'fuentes'=>$fuentes
            ]

            ],200);
    } 
    
    public function etiquetas(){
        $etiquetas = DB::table('etiquetas')->get();

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'etiquetas'=>$etiquetas
            ]
            ],200);

    }

    public function colaboradores(){
        $colaboradores = DB::table('users')
                        ->join('detalle_colaborador','users.id','detalle_colaborador.id_colaborador')
                        ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                        ->select('users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto','users.email','detalle_colaborador.telefono','users.created_at')
                        ->get();
        
        return response()->json([
                    'message'=>'Success',
                    'error'=>false,
                    'data'=>[
                        'colaboradores'=>$colaboradores
                    ]
                    ],200);
    }

    public function status_oportunidades(){
        $status = DB::table('cat_status_oportunidad')->get();
        
        return response()->json([
                    'message'=>'Success',
                    'error'=>false,
                    'data'=>[
                        'status'=>$status
                    ]
                    ],200);

    }

    public function servicios(){
        $servicios = DB::table('cat_servicios')->get();
        
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'servicios'=>$servicios
            ]
            ],200);
    }


    
    //AUX
    public function guard()
    {
        return Auth::guard();
    }

    public function colorsOportunidades($id){
        $result = DB::table('cat_status_oportunidad')->select('cat_status_oportunidad.color')->where('id_cat_status_oportunidad',$id)->first();
        return $result->color;
    }

}
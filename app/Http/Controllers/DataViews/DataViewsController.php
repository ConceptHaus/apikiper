<?php

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\CatFuente;
use App\Modelos\User;
use App\Modelos\Extras\Etiqueta;
use App\Modelos\Oportunidad\CatServicios;
use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Modelos\Prospecto\CatMedioContacto;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\MedioContactoProspecto;
use App\Modelos\Oportunidad\MedioContactoOportunidad;

use App\Modelos\Extras\RecordatorioProspecto;
use Mailgun;
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


        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');

        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
                    ->groupBy('cat_fuentes.nombre')->get();


        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen
            ]
            ],200);

    }
    public function dashboardSemanal(){
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial
        $semana = new Carbon('last week');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($semana ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($semana ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $colaboradores = DB::table('users')
                                ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                                ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->select('users.nombre','users.apellido','detalle_colaborador.puesto','fotos_colaboradores.url_foto',DB::raw('count(*) as oportunidades_cerradas, users.id'))
                                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                                ->whereBetween('status_oportunidad.created_at', array($semana ,$hoy))
                                ->groupBy('users.id')
                                ->orderBy('oportunidades_cerradas','desc')->limit(5)->get();

        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->whereBetween('status_prospecto.updated_at', array($semana ,$hoy))
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->whereBetween('detalle_oportunidad.updated_at', array($semana ,$hoy))
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');

        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
                    ->whereBetween('prospectos.created_at', array($semana ,$hoy))
                    ->groupBy('cat_fuentes.nombre')->get();


        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen
            ]
            ],200);

    }

    public function dashboardMensual(){
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial
        $mes = new Carbon('last month');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($mes ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($mes ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $colaboradores = DB::table('users')
                                ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                                ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->select('users.nombre','users.apellido','detalle_colaborador.puesto','fotos_colaboradores.url_foto',DB::raw('count(*) as oportunidades_cerradas, users.id'))
                                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                                ->whereBetween('status_oportunidad.updated_at', array($mes ,$hoy))
                                ->groupBy('users.id')
                                ->orderBy('oportunidades_cerradas','desc')->limit(5)->get();




        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->whereBetween('prospectos.created_at', array($mes ,$hoy))
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->whereBetween('status_oportunidad.updated_at', array($mes ,$hoy))
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');

        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
                    ->whereBetween('prospectos.created_at', array($mes ,$hoy))
                    ->groupBy('cat_fuentes.nombre')->get();

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen
            ]
            ],200);

    }

    public function dashboardAnual(){
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial
        $anio = new Carbon('last year');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($anio ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',2)->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->whereBetween('status_oportunidad.updated_at', array($anio ,$hoy))
                                    ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',1)->count();

        $colaboradores = DB::table('users')
                                ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                                ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->select('users.nombre','users.apellido','detalle_colaborador.puesto','fotos_colaboradores.url_foto',DB::raw('count(*) as oportunidades_cerradas, users.id'))
                                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                                ->whereBetween('status_oportunidad.updated_at', array($anio ,$hoy))
                                ->groupBy('users.id')
                                ->orderBy('oportunidades_cerradas','desc')->limit(5)->get();



        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->whereBetween('prospectos.created_at', array($anio ,$hoy))
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->whereBetween('status_oportunidad.updated_at', array($anio ,$hoy))
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');

        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
                    ->whereBetween('prospectos.created_at', array($anio ,$hoy))
                    ->groupBy('cat_fuentes.nombre')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen
            ]
            ],200);

    }

    public function prospectos(){
        $total_prospectos = Prospecto::all()->count();

        $nocontactados_prospectos = DB::table('prospectos')
                                    ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                    ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();


        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();

        $prospectos_t= DB::table('prospectos')
                            ->join('detalle_prospecto','prospectos.id_prospecto','detalle_prospecto.id_prospecto')
                            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->select('prospectos.id_prospecto',
                                    'prospectos.nombre',
                                    'prospectos.apellido',
                                    'prospectos.correo',
                                    'detalle_prospecto.telefono',
                                    'cat_fuentes.nombre as fuente_nombre',
                                    'cat_fuentes.nombre as fuente_url',
                                    'prospectos.created_at')->get();

        $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('status_prospecto.status')
                                ->orderBy('prospectos.created_at','desc')
                                ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'prospectos'=>$prospectos,
                'prospectos_total'=>$total_prospectos,
                'prospectos_nocontactados'=> $nocontactados_prospectos,
                'prospectos_fuente'=>$this->FuentesChecker($catalogo_fuentes,$origen)
            ]
            ],200);
    }
    public function prospectosstatus($status){
        $prospectos = DB::table('prospectos')
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono','detalle_prospecto.empresa','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status')
                        ->orderBy('status_prospecto.updated_at','desc')
                        ->get();

        return response()->json([
            'message'=>'Correcto',
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
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.nombre as fuente_url','oportunidades.created_at')
                            ->orderBy('oportunidades.created_at','desc')
                            ->get();


        return response()->json([
            'message'=>'Correcto',
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
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->join('users','users.id', 'colaborador_oportunidad.id_colaborador')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_status_oportunidad.color as color_status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.nombre as fuente_url','oportunidades.created_at','users.id as id_colaborador', 'users.nombre as asignado_nombre', 'users.apellido as asignado_apellido')
                            ->orderBy('status_oportunidad.updated_at','desc')
                            ->get();


        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total'=>[
                    'valor'=>$oportunidades_total,
                    'porcentaje'=>100,
                    'color'=>'#4646B9'

                ],
                'cotizadas'=>[
                    'valor'=>$oportunidades_cotizadas,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_cotizadas,$oportunidades_total),
                    'color'=>$this->colorsOportunidades(1)

                ],
                'cerradas'=>[
                    'valor'=>$oportunidades_cerradas,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_cerradas,$oportunidades_total),
                    'color'=>$this->colorsOportunidades(2)

                ],
                'no_viables'=>[
                    'valor'=>$oportunidades_no_viables,
                    'porcentaje'=>$this->porcentajeOportunidades($oportunidades_no_viables,$oportunidades_total),
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
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad',$status)
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                            ->get();

        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','users.id', 'colaborador_oportunidad.id_colaborador')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','cat_status_oportunidad.status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_status_oportunidad.color as color_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','users.id as id_colaborador','users.nombre as asignado_nombre', 'users.apellido as asignado_apellido','oportunidades.created_at')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',intval($status))
                            ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();



        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'status'=>$nombre_status->status,
                'total'=>[
                    'valor'=>$total,
                    'porcentaje'=>$this->porcentajeOportunidades($total,$total_general),
                    'color'=>$this->colorsOportunidades($status)
                ],
                'fuentes'=> $this->fuentesChecker($catalogo_fuentes, $fuentes),
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
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')->get();

        $status = DB::table('cat_status_oportunidad')
                      ->select('id_cat_status_oportunidad as id','status','color')->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'cotizadas'=>$oportunidades_cotizadas,
                'cerradas'=>$oportunidades_cerradas,
                'no_viables'=>$oportunidades_no_viables,
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
                'status'=>$status
            ]
            ],200);

    }

    public function estadisticas_colaborador(){
        $users_ventas = DB::table('users')
                        ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                        ->join('oportunidades','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                        ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                        ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                        ->select('users.id','users.email','users.nombre',DB::raw("SUM(detalle_oportunidad.valor) as valor_total"))
                        ->where('status_oportunidad.id_cat_status_oportunidad',2)
                        ->groupBy('users.email')->orderBy('valor_total','desc')->limit(10)->get();


        $top_3 = DB::table('users')
                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                ->join('oportunidades','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto',DB::raw('count(*) as cerradas, users.email'))
                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                ->groupBy('users.email')->orderBy('cerradas','desc')->limit(3)->get();


        $colaboradores = DB::table('users')
                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                ->join('detalle_colaborador','detalle_colaborador.id_colaborador', 'users.id')
                ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                ->select('users.id','users.nombre','users.apellido','users.email','detalle_colaborador.telefono','fotos_colaboradores.url_foto',DB::raw('count(status_oportunidad.id_cat_status_oportunidad) as total'),DB::raw('count(status_oportunidad.id_cat_status_oportunidad = 2) as cerradas'), DB::raw('count(status_oportunidad.id_cat_status_oportunidad = 1) as cotizadas'))
                ->groupBy('users.id')
                ->get();

        return response()->json([
            'message'=>'Correcto',
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
                    ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
                    ->groupBy('users.id')
                    ->orderBy('total_ingresos','desc')
                    ->limit(3)
                    ->get();

        $fuentes = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->select(DB::raw('SUM(detalle_oportunidad.valor) as total'),'cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total_cotizado'=>number_format($total_cotizado,2),
                'total_cerrador'=>number_format($total_cerrador,2),
                'total_noviable'=>number_format($total_noviable,2),
                'top_3'=>$top_3,
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes,$fuentes)
            ]

            ],200);
    }

    public function estadisticas_finanzas_semanal(){
      $semana = new Carbon('last week');
      $hoy = new Carbon('now');

        $total_cotizado = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)
                            ->whereBetween('oportunidades.created_at', array($semana ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_cerrador = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                            ->whereBetween('oportunidades.created_at', array($semana ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_noviable = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)
                            ->whereBetween('oportunidades.created_at', array($semana ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $top_3 = DB::table('users')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                    ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                    ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                    ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
                    ->groupBy('users.id')
                    ->orderBy('total_ingresos','desc')
                    ->limit(3)
                    ->get();

        $fuentes = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->whereBetween('oportunidades.created_at', array($semana ,$hoy))
                    ->select(DB::raw('SUM(detalle_oportunidad.valor) as total'),'cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total_cotizado'=>number_format($total_cotizado,2),
                'total_cerrador'=>number_format($total_cerrador,2),
                'total_noviable'=>number_format($total_noviable,2),
                'top_3'=>$top_3,
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes,$fuentes)
            ]

            ],200);
    }

    public function estadisticas_finanzas_mensual(){
      $mes = new Carbon('last month');
      $hoy = new Carbon('now');

        $total_cotizado = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)
                            ->whereBetween('oportunidades.created_at', array($mes ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_cerrador = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                            ->whereBetween('oportunidades.created_at', array($mes ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_noviable = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)
                            ->whereBetween('oportunidades.created_at', array($mes ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $top_3 = DB::table('users')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                    ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                    ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                    ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
                    ->groupBy('users.id')
                    ->orderBy('total_ingresos','desc')
                    ->limit(3)
                    ->get();

        $fuentes = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->whereBetween('oportunidades.created_at', array($mes ,$hoy))
                    ->select(DB::raw('SUM(detalle_oportunidad.valor) as total'),'cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total_cotizado'=>number_format($total_cotizado,2),
                'total_cerrador'=>number_format($total_cerrador,2),
                'total_noviable'=>number_format($total_noviable,2),
                'top_3'=>$top_3,
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes,$fuentes)
            ]

            ],200);
    }

    public function estadisticas_finanzas_anual(){
      $anio = new Carbon('last year');
      $hoy = new Carbon('now');

        $total_cotizado = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1)
                            ->whereBetween('oportunidades.created_at', array($anio ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_cerrador = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                            ->whereBetween('oportunidades.created_at', array($anio ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $total_noviable = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',3)
                            ->whereBetween('oportunidades.created_at', array($anio ,$hoy))
                            ->sum('detalle_oportunidad.valor');

        $top_3 = DB::table('users')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                    ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                    ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                    ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
                    ->groupBy('users.id')
                    ->orderBy('total_ingresos','desc')
                    ->limit(3)
                    ->get();

        $fuentes = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->whereBetween('oportunidades.created_at', array($anio ,$hoy))
                    ->select(DB::raw('SUM(detalle_oportunidad.valor) as total'),'cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'total_cotizado'=>number_format($total_cotizado,2),
                'total_cerrador'=>number_format($total_cerrador,2),
                'total_noviable'=>number_format($total_noviable,2),
                'top_3'=>$top_3,
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes,$fuentes)
            ]

            ],200);
    }

    public function etiquetas(){
        $etiquetas = DB::table('etiquetas')
        ->where('status',1)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'DESC')
        ->get();

        if ($etiquetas) {
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>[
                  'etiquetas'=>$etiquetas
              ]
              ],200);
        }
        return response()->json([
            'message'=>'No hay etiquetas',
            'error'=>false
            ],200);

    }

    public function getEtiquetasAjustes(){
      $etiquetas = DB::table('etiquetas')
      // ->where('status',1)
      ->whereNull('deleted_at')
      ->orderBy('created_at', 'DESC')
      ->get();

      if ($etiquetas) {
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'etiquetas'=>$etiquetas
            ]
            ],200);
      }
      return response()->json([
          'message'=>'No hay etiquetas',
          'error'=>false
          ],200);
    }

    public function colaboradores(){
        $colaboradores = DB::table('users')
                        ->join('detalle_colaborador','users.id','detalle_colaborador.id_colaborador')
                        ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                        ->select('users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto','users.email','detalle_colaborador.telefono','users.created_at')
                        ->get();

        if ($colaboradores) {
          return response()->json([
                      'message'=>'Correcto',
                      'error'=>false,
                      'data'=>[
                          'colaboradores'=>$colaboradores
                      ]
                      ],200);
        }
        return response()->json([
                    'message'=>'No hay colaboradores',
                    'error'=>false
                    ],200);
    }

    public function status_oportunidades(){
        $status_1 = DB::table('cat_status_oportunidad')
                        ->where('id_cat_status_oportunidad',1)
                        ->select('id_cat_status_oportunidad as id','status as nombre','descripcion','color')
                        ->first();


        $status_2 = DB::table('cat_status_oportunidad')
                        ->where('id_cat_status_oportunidad',2)
                        ->select('id_cat_status_oportunidad as id', 'status as nombre','descripcion','color')
                        ->first();

        $status_3 = DB::table('cat_status_oportunidad')
                        ->where('id_cat_status_oportunidad',3)
                        ->select('id_cat_status_oportunidad as id', 'status as nombre','descripcion','color')
                        ->first();

        return response()->json([
                    'message'=>'Correcto',
                    'error'=>false,
                    'data'=>[
                        'status_1'=>$status_1,
                        'status_2'=>$status_2,
                        'status_3'=>$status_3
                    ]
                    ],200);

    }

    public function servicios(){
        $servicios = DB::table('cat_servicios')
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'DESC')
        ->get();

        if ($servicios) {
          return response()->json([
              'message'=>'Correcto',
              'error'=>false,
              'data'=>[
                  'servicios'=>$servicios
              ]
              ],200);
        }
        return response()->json([
            'message'=>'No hay servicios.',
            'error'=>false
            ],200);
    }

    //POST
    public function addEtiquetas(Request $request){
        $validador = $this->validadorEtiqueta($request->all());

        if($validador->passes()){
            try{
                DB::beginTransaction();
                $etiqueta = new Etiqueta;
                $etiqueta->nombre = $request->nombre;
                $etiqueta->descripcion = $request->descripcion;
                $etiqueta->save();

                DB::commit();

                return response()->json([
                    'error'=>false,
                    'message'=>'Registro Correcto',
                    'data'=>$etiqueta
                ],200);
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e
                ],400);
            }


        }
        $errores = $validador->errors()->toArray();
        return response()->json([
            'error'=>true,
            'message'=>$errores
        ],400);

    }

    //PUT
    public function updateEtiquetas(Request $request){

        $id = $request->id_etiqueta;

        try{
            DB::beginTransaction();

            $etiqueta = Etiqueta::where('id_etiqueta',$id)->first();
            $etiqueta->nombre = $request->nombre;
            $etiqueta->descripcion = $request->descripcion;
            $etiqueta->status = $request->status;
            $etiqueta->save();

            DB::commit();

            return response()->json([
                    'error'=>false,
                    'message'=>'Registo Correcto',
                    'data'=>$etiqueta
                ],200);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'error'=>true,
                'message'=>$e
            ],400);
        }

    }

    public function deleteEtiquetas($id){
        $etiqueta  = Etiqueta::where('id_etiqueta',$id)->first();

        if($etiqueta){
          try {
            DB::beginTransaction();
            $etiqueta->delete();
            DB::commit();

              return response()->json([
                  'message'=>'Borrado Correctamente',
                  'error'=>false,
              ]);

          } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
              'error'=>true,
              'message'=>$e
            ],400);
          }


        }
        return response()->json([
            'message'=>'No se encontro la etiqueta.',
            'error'=>false
        ],200);

    }

    //POST
    public function addServicios(Request $request){
        $validador = $this->validadorEtiqueta($request->all());

        if($validador->passes()){
            try{
                DB::beginTransaction();
                $servicio = new CatServicios;
                $servicio->nombre = $request->nombre;
                $servicio->descripcion = $request->descripcion;
                $servicio->save();

                DB::commit();

                return response()->json([
                    'error'=>false,
                    'message'=>'Registro Correcto',
                    'data'=>$servicio
                ],200);

            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'error'=>true,
                    'message'=>$e
                ],400);
            }
        }
        $errores = $validador->errors()->toArray();
        return response()->json([
            'error'=>true,
            'message'=>$errores
        ],400);
    }

    //PUT
    public function updateServicios(Request $request){
        $id = $request->id_servicio;

        try{
            DB::beginTransaction();
            $servicio = CatServicios::where('id_servicio_cat',$id)->first();
            $servicio->nombre = $request->nombre;
            $servicio->descripcion = $request->descripcion;
            $servicio->status = $request->status;
            $servicio->save();
            DB::commit();

            return response()->json([
                'error'=>false,
                'message'=>'Registro Correcto',
                'data'=>$servicio
            ]);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'error'=>true,
                'message'=>$e

            ],400);
        }
    }

    public function deleteServicios($id){
        $servicios = CatServicios::where('id_servicio_cat',$id)->first();

        if($servicios){
          try {
            DB::beginTransaction();
            $servicios->delete();
            DB::commit();

            return response()->json([
                'message'=>'Borrado Correctamente',
                'error'=>false,
            ],200);
          } catch (Exception $e) {
            return response()->json([
                'message'=>$e,
                'error'=>true
            ],400);
          }

        }
        return response()->json([
            'message'=>'No se encontro el servicio.',
            'error'=>false
        ],200);
    }

    public function updateStatus(Request $request){
        $id = $request->id_status;
        try{
            DB::beginTransaction();
            $status = CatStatusOportunidad::where('id_cat_status_oportunidad',$id)->first();
            $status->status = $request->status;
            $status->descripcion = $request->descripcion;
            $status->color = $request->color;
            $status->save();
            DB::commit();

            return response()->json([
                'error'=>false,
                'message'=>'Actualizado Correctamente',
                'data'=>$status
            ]);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'error'=>true,
                'message'=>$e
            ],400);
        }
    }


    //AUX
    public function validadorEtiqueta(array $data){
        return Validator::make($data,[
            'nombre'=>'required|string',

        ]);
    }

    public function validatorMail(array $data){
      return Validator::make($data,[
          'email_de'=>'required|email',
          'nombre_de'=>'string|max:255',
          'email_para'=>'required|email',
          'nombre_para'=>'string|max:255',
          'asunto'=>'required|string|max:255',
          'contenido'=>'required'
      ]);
    }

    public function validatorMedioContactoProspecto(array $data){

      if ($data['id_mediocontacto_catalogo'] == 1) {
        return Validator::make($data, [
          'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
          'id_prospecto'=>'required|exists:prospectos,id_prospecto',
          'descripcion'=>'required|string|max:255'
          // 'fecha'=>'required',
          // 'hora'=>'required',
          // 'lugar'=>'required|string|max:255'
        ]);
      }
      if ($data['id_mediocontacto_catalogo'] == 5) {
        return Validator::make($data, [
          'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
          'id_prospecto'=>'required|exists:prospectos,id_prospecto',
          'descripcion'=>'required|string|max:255',
          'fecha'=>'required',
          'hora'=>'required',
          'lugar'=>'required|string|max:255'
        ]);
      }

      return Validator::make($data, [
        'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
        'id_prospecto'=>'required|exists:prospectos,id_prospecto',
        'descripcion'=>'required|string|max:255',
        'fecha'=>'required',
        'hora'=>'required'
      ]);
    }

    public function validatorMedioContactoOportunidad(array $data){

      if ($data['id_mediocontacto_catalogo'] == 1) {
        return Validator::make($data, [
          'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
          'id_oportunidad'=>'required|exists:oportunidades,id_oportunidad',
          'descripcion'=>'required|string|max:255'
          // 'fecha'=>'required',
          // 'hora'=>'required',
          // 'lugar'=>'required|string|max:255'
        ]);
      }
      if ($data['id_mediocontacto_catalogo'] == 5) {
        return Validator::make($data, [
          'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
          'id_oportunidad'=>'required|exists:oportunidades,id_oportunidad',
          'descripcion'=>'required|string|max:255',
          'fecha'=>'required',
          'hora'=>'required',
          'lugar'=>'required|string|max:255'
        ]);
      }

      return Validator::make($data, [
        'id_mediocontacto_catalogo'=>'required|exists:mediocontacto_catalogo,id_mediocontacto_catalogo',
        'id_oportunidad'=>'required|exists:oportunidades,id_oportunidad',
        'descripcion'=>'required|string|max:255',
        'fecha'=>'required',
        'hora'=>'required'
      ]);
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function colorsOportunidades($id){
        $result = DB::table('cat_status_oportunidad')->select('cat_status_oportunidad.color')->where('id_cat_status_oportunidad',$id)->first();
        return $result->color;
    }

    //Extras
    public function getAllMedioContacto(){
      $medio_contacto = CatMedioContacto::select('id_mediocontacto_catalogo as id', 'nombre','color')->get();

      if ($medio_contacto) {
        return response()->json([
          'error'=>false,
          'message'=>'Medios de contacto obtenidos correctamente.',
          'data'=>$medio_contacto
        ],200);
      }

      return response()->json([
        'error'=>true,
        'message'=>'No se encontro el medio de contacto.'
      ],400);
    }

    public function getMedioContacto($id){

      $medio_contacto=MedioContactoProspecto::where('id_prospecto',$id)
                      ->join('mediocontacto_catalogo','mediocontacto_catalogo.id_mediocontacto_catalogo','medio_contacto_prospectos.id_mediocontacto_catalogo')
                      ->get();

      if ($medio_contacto->isEmpty()) {
        return response()->json([
          'error'=>false,
          'message'=>'No tiene medios de contacto.'
        ],200);
      }

      return response()->json([
        'error'=>false,
        'message'=>'Medios de contacto obtenidos correctamente.',
        'data'=>$medio_contacto
      ],200);
    }

    public function getMedioContactoOportunidad($id){

      $medio_contacto = MedioContactoOportunidad::where('id_oportunidad',$id)
                                                  ->join('mediocontacto_catalogo','mediocontacto_catalogo.id_mediocontacto_catalogo','medio_contacto_oportunidades.id_mediocontacto_catalogo')
                                                  ->get();

      if ($medio_contacto->isEmpty()) {
        return response()->json([
          'error'=>false,
          'message'=>'No tiene medios de contacto.'
        ],200);
      }

      return response()->json([
        'error'=>false,
        'message'=>'Medios de contacto obtenidos correctamente.',
        'data'=>$medio_contacto
      ],200);
    }

    public function getFuentes(){
        $fuentes = CatFuente::all();

        if ($fuentes) {
          return response()->json([
              'error'=>false,
              'message'=>'Fuentes obtenidas correctamente.',
              'data'=>$fuentes
          ],200);
        }

        return response()->json([
            'error'=>false,
            'message'=>'No se encontraron fuentes.'
        ],200);
    }
    public function sendMail (Request $request){
      $data = $request->all();
      $validator = $this->validatorMail($data);

      if ($validator->passes()) {


        if($request->id_prospecto){

            DB::beginTransaction();
            $prospecto = StatusProspecto::where('id_prospecto',$request->id_prospecto)->first();
            $prospecto->id_cat_status_prospecto = 2;
            $prospecto->save();
            DB::commit();
        }


        Mailgun::send('mailing.mail', $data, function ($message) use ($data){
           // $message->tag('myTag');
           $message->from($data['email_de'],$data['nombre_de']);
           // $message->testmode(true);
           $message->subject($data['asunto']);
           $message->to($data['email_para'],$data['nombre_para']);
       });

       return response()->json([
         'error'=>false,
         'message'=>'Mail enviado correctamente',
       ],200);
      }

      $errores = $validator->errors()->toArray();
      return response()->json([
        'error'=>true,
        'message'=>$errores
      ],400);
    }

    public function porcentajeOportunidades($oportunidad, $total){
        if($oportunidad == 0){
            return intval($oportunidad);
        }
        return intval(round($oportunidad*100/$total));
    }

    public function cambioStatusProspecto ($id){

      try {
        DB::beginTransaction();
        $statusProspecto = StatusProspecto::where('id_prospecto',$id);
        $statusProspecto->id_cat_status_prospecto = 2;
        $statusProspecto->save();
        DB::commit();
      }catch (Exception $e) {
        DB::rollBack();

        return response()->json([
          'error'=>true,
          'message'=>$e
        ],400);
      }

    }

    public function FuentesChecker($catalogo,$consulta){

            if(count($catalogo) > count($consulta)){

                if(count($consulta) == 0){

                    foreach($catalogo as $fuente){
                        $fuente->total=0;

                    }
                    return $catalogo;
                }
                else{
                    $collection = collect($consulta);
                    for($i = 0; $i<count($catalogo); $i++){
                        $match = false;
                        for($j=0; $j<count($consulta); $j++){

                            if( $catalogo[$i]->nombre == $consulta[$j]->nombre ){
                                $match = true;
                                break;
                            }
                        }

                        if(!$match){
                            $catalogo[$i]->total = 0;
                            $collection->push($catalogo[$i]);
                        }
                    }
                    return $collection->all();
                }



            }
            return $consulta;

    }




    public function addMedioContactoProspecto(Request $request){

      $validator = $this->validatorMedioContactoProspecto($request->all());

      if ($validator->passes()) {
        try {
          DB::beginTransaction();
          $medio_contacto_prospecto = new MedioContactoProspecto;
          $medio_contacto_prospecto->id_mediocontacto_catalogo = $request->id_mediocontacto_catalogo;
          $medio_contacto_prospecto->id_prospecto = $request->id_prospecto;
          $medio_contacto_prospecto->descripcion = $request->descripcion;
          $medio_contacto_prospecto->fecha = $request->fecha;
          $medio_contacto_prospecto->hora = $request->hora;
          $medio_contacto_prospecto->lugar =$request->lugar;
          $medio_contacto_prospecto->save();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Medio de contacto agregado correctamente.'
          ],200);

        } catch (Exception $e) {
          DB::rollBack();

          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }
      }
      $errores = $validator->errors()->toArray();
      return response()->json([
        'error'=>true,
        'message'=>$errores
      ],400);
    }

    public function addMedioContactoOportunidad(Request $request){

      $validator = $this->validatorMedioContactoOportunidad($request->all());

      if ($validator->passes()) {
        try {
          DB::beginTransaction();
          $medio_contacto_oportunidad = new MedioContactoOportunidad;
          $medio_contacto_oportunidad->id_mediocontacto_catalogo = $request->id_mediocontacto_catalogo;
          $medio_contacto_oportunidad->id_oportunidad = $request->id_oportunidad;
          $medio_contacto_oportunidad->descripcion = $request->descripcion;
          $medio_contacto_oportunidad->fecha = $request->fecha;
          $medio_contacto_oportunidad->hora = $request->hora;
          $medio_contacto_oportunidad->lugar =$request->lugar;
          $medio_contacto_oportunidad->save();
          DB::commit();

          return response()->json([
            'error'=>false,
            'message'=>'Medio de contacto agregado correctamente.'
          ],200);

        } catch (Exception $e) {
          DB::rollBack();

          return response()->json([
            'error'=>true,
            'message'=>$e
          ],400);
        }
      }
      $errores = $validator->errors()->toArray();
      return response()->json([
        'error'=>true,
        'message'=>$errores
      ],400);
    }

}

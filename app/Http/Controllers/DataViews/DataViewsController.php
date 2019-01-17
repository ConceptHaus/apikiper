<?php

namespace App\Http\Controllers\DataViews;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\CalendarLinks\Link;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;

use App\Modelos\Prospecto\Prospecto;
use App\Modelos\Prospecto\StatusProspecto;
use App\Modelos\Prospecto\CatFuente;
use App\Modelos\User;
use App\Modelos\Extras\Etiqueta;
use App\Modelos\Oportunidad\CatServicios;
use App\Modelos\Oportunidad\CatStatusOportunidad;
use App\Modelos\Prospecto\CatMedioContacto;
use App\Modelos\Prospecto\MedioContactoProspecto;
use App\Modelos\Oportunidad\MedioContactoOportunidad;
use App\Modelos\Extras\EventoProspecto;
use App\Modelos\Extras\DetalleEventoProspecto;
use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\DetalleRecordatorioProspecto;

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
                                    ->wherenull('oportunidades.deleted_at')
                                    ->wherenull('status_oportunidad.deleted_at')
                                    ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
                                    ->count();

        $oportunidades_cotizadas =  DB::table('oportunidades')
                                    ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
                                    ->wherenull('oportunidades.deleted_at')
                                    ->wherenull('status_oportunidad.deleted_at')
                                    ->where('status_oportunidad.id_cat_status_oportunidad','=',1)
                                    ->count();

        $colaboradores = DB::table('users')
                                ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                                ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
                                ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                                ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                                ->wherenull('detalle_colaborador.deleted_at')
                                ->wherenull('users.deleted_at')
                                ->wherenull('fotos_colaboradores.deleted_at')
                                ->wherenull('colaborador_oportunidad.deleted_at')
                                ->wherenull('status_oportunidad.deleted_at')
                                ->select('users.nombre','users.apellido','detalle_colaborador.puesto','fotos_colaboradores.url_foto',DB::raw('count(*) as oportunidades_cerradas, users.id'))
                                ->where('status_oportunidad.id_cat_status_oportunidad',2)
                                ->groupBy('users.id')
                                ->orderBy('oportunidades_cerradas','desc')->limit(5)->get();


        $prospectos_sin_contactar = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->wherenull('prospectos.deleted_at')
                                ->wherenull('status_prospecto.deleted_at')
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();

        $ingresos = DB::table('oportunidades')
                    ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('detalle_oportunidad.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->where('status_oportunidad.id_cat_status_oportunidad',2)
                    ->sum('detalle_oportunidad.valor');

        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('cat_fuentes.deleted_at')
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
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $period = CarbonPeriod::create($inicioSemana->toDateString(), $finSemana->toDateString());

        $semana = new Carbon('last week');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = $this->oportunidades_por_periodo_por_status($inicioSemana,$finSemana,2);
        $oportunidades_cotizadas = $this->oportunidades_por_periodo_por_status($inicioSemana,$finSemana,1);
        $colaboradores = $this->dashboard_colaboradores_periodo($inicioSemana,$finSemana);
        $prospectos_sin_contactar = $this->prospectos_por_periodo_por_status($inicioSemana,$finSemana,2);
        $ingresos = $this->ingresos_por_periodo_por_status($inicioSemana,$finSemana,2);
        $origen = $this->origen_por_periodo($inicioSemana, $finSemana);

        if(Activity::all()->last() != null){

            $activity = Activity::all()->last()->whereBetween('created_at',array($inicioSemana ,$finSemana))->orderBy('created_at','desc')->get();
        }else{
            
            $activity = null;
        }
        
        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen,
                'activity'=>$activity

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
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        $mes = new Carbon('last month');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = $this->oportunidades_por_periodo_por_status($inicioMes, $finMes, 2);
        $oportunidades_cotizadas =  $this->oportunidades_por_periodo_por_status($inicioMes, $finMes, 1);
        $colaboradores = $this->dashboard_colaboradores_periodo($inicioMes,$finMes);
        $prospectos_sin_contactar = $this->prospectos_por_periodo_por_status($inicioMes, $finMes, 2);
        $ingresos = $this->ingresos_por_periodo_por_status($inicioMes, $finMes, 2);
        $origen = $this->origen_por_periodo($inicioMes, $finMes);
        
        if(Activity::all()->last() != null){

            $activity = Activity::all()->last()->whereBetween('created_at',array($inicioMes ,$finMes))->orderBy('created_at','desc')->get();
        }else{
            
            $activity = null;
        }

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen,
                'activity'=>$activity

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
        $inicioAnio = Carbon::now()->startOfYear();
        $finAnio = Carbon::now()->endOfYear();
        $anio = new Carbon('last year');
        $hoy = new Carbon('now');

        $oportuniades_cerradas = $this->oportunidades_por_periodo_por_status($inicioAnio, $finAnio, 2);
        $oportunidades_cotizadas =  $this->oportunidades_por_periodo_por_status($inicioAnio, $finAnio, 1);
        $colaboradores = $this->dashboard_colaboradores_periodo($inicioAnio,$finAnio);
        $prospectos_sin_contactar = $this->prospectos_por_periodo_por_status($inicioAnio, $finAnio, 2);
        $ingresos = $this->ingresos_por_periodo_por_status($inicioAnio, $finAnio, 2);
        $origen = $this->origen_por_periodo($inicioAnio, $finAnio);

        if(Activity::all()->last() != null){

            $activity = Activity::all()->last()->whereBetween('created_at',array($inicioAnio ,$finAnio))->orderBy('created_at','desc')->get();
        }else{
            
            $activity = null;
        }
        
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'oportunidades_cerradas'=>number_format($oportuniades_cerradas),
                'oportunidades_cotizadas'=>number_format($oportunidades_cotizadas),
                'prospectos_sin_contactar'=>number_format($prospectos_sin_contactar),
                'colaboradores'=>$colaboradores,
                'ingresos'=>number_format($ingresos,2),
                'origen_prospecto'=>$origen,
                'activity'=>$activity
            ]
            ],200);

    }

    public function prospectos(){

        $total_prospectos = Prospecto::all()->count();

        $nocontactados_prospectos = DB::table('prospectos')
                                    ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                    ->wherenull('prospectos.deleted_at')
                                    ->wherenull('prospectos.deleted_at')
                                    ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();


        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->wherenull('prospectos.deleted_at')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();

        $prospectos = Prospecto::with('detalle_prospecto')
                                ->wherenull('prospectos.deleted_at')
                                ->with('status_prospecto.status')
                                ->orderBy('prospectos.created_at','desc')
                                ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->wherenull('cat_fuentes.deleted_at')
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
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('detalle_prospecto.deleted_at')
                        ->whereNull('status_prospecto.deleted_at')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono','detalle_prospecto.empresa','detalle_prospecto.whatsapp','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status')
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
                            ->whereNull('oportunidades.deleted_at')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)->count();

        $oportunidades_cotizadas = $this->oportunidades_por_colaborador_por_status($id,1);

        $oportunidades_cerradas = $this->oportunidades_por_colaborador_por_status($id,2);
                    
        $oportunidades_no_viables = $this->oportunidades_por_colaborador_por_status($id,3);

        $oportunidades = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','cat_status_oportunidad.status','cat_servicios.nombre as servicio','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.nombre as fuente_url','oportunidades.created_at')
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
                            ->whereNull('oportunidades.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id)->count();

        $oportunidades_cotizadas = $this->oportunidades_por_colaborador_por_status($id,1);

        $oportunidades_cerradas = $this->oportunidades_por_colaborador_por_status($id,2);

        $oportunidades_no_viables = $this->oportunidades_por_colaborador_por_status($id,3);

        $oportunidades = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
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
                            ->whereNull('oportunidades.deleted_at')
                            ->whereNull('detalle_oportunidad.deleted_at')
                            ->whereNull('colaborador_oportunidad.deleted_at')
                            ->whereNull('oportunidad_prospecto.deleted_at')
                            ->whereNull('prospectos.deleted_at')
                            ->whereNull('status_oportunidad.deleted_at')
                            ->whereNull('servicio_oportunidad.deleted_at')
                            ->whereNull('users.deleted_at')
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','cat_status_oportunidad.status','cat_status_oportunidad.color as color_status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.nombre as fuente_url','oportunidades.created_at','users.id as id_colaborador', 'users.nombre as asignado_nombre', 'users.apellido as asignado_apellido')
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
                            ->wherenull('oportunidades.deleted_at')
                            ->wherenull('colaborador_oportunidad.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id)->count();
        $total = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->wherenull('oportunidades.deleted_at')
                            ->wherenull('colaborador_oportunidad.deleted_at')
                            ->wherenull('status_oportunidad.deleted_at')
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
                            ->whereNull('oportunidades.deleted_at')
                            ->wherenull('colaborador_oportunidad.deleted_at')
                            ->wherenull('oportunidad_prospecto.deleted_at')
                            ->wherenull('prospectos.deleted_at')
                            ->wherenull('status_oportunidad.deleted_at')
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
                            ->get();

        $oportunidades = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('users','users.id', 'colaborador_oportunidad.id_colaborador')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                            ->join('servicio_oportunidad','servicio_oportunidad.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                            ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                            ->whereNull('oportunidades.deleted_at')
                            ->wherenull('colaborador_oportunidad.deleted_at')
                            ->wherenull('detalle_oportunidad.deleted_at')
                            ->wherenull('users.deleted_at')
                            ->wherenull('oportunidad_prospecto.deleted_at')
                            ->wherenull('prospectos.deleted_at')
                            ->wherenull('status_oportunidad.deleted_at')
                            ->wherenull('servicio_oportunidad.deleted_at')->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','cat_status_oportunidad.status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_status_oportunidad.color as color_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','users.id as id_colaborador','users.nombre as asignado_nombre', 'users.apellido as asignado_apellido','oportunidades.created_at')
                            ->where('colaborador_oportunidad.id_colaborador','=',$id)
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',intval($status))
                            ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->whereNull('cat_fuentes.deleted_at')
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
        $oportunidades_cotizadas = $this->oportunidades_por_status(1);

        $oportunidades_cerradas = $this->oportunidades_por_status(2);

        $oportunidades_no_viables = $this->oportunidades_por_status(3);

        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->whereNull('oportunidades.deleted_at')
                            ->whereNull('colaborador_oportunidad.deleted_at')
                            ->whereNull('oportunidad_prospecto.deleted_at')
                            ->whereNull('prospectos.deleted_at')
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
                        ->whereNull('colaborador_oportunidad.deleted_at')
                        ->whereNull('oportunidades.deleted_at')
                        ->whereNull('users.deleted_at')
                        ->whereNull('detalle_oportunidad.deleted_at')
                        ->whereNull('status_oportunidad.deleted_at')
                        ->select('users.id','users.email','users.nombre',DB::raw("SUM(detalle_oportunidad.valor) as valor_total"))
                        ->where('status_oportunidad.id_cat_status_oportunidad',2)
                        ->groupBy('users.email')->orderBy('valor_total','desc')->limit(10)->get();

        $selects = array(
            'users.apellido as apellido',
            'count(colaborador_oportunidad.id_colaborador_oportunidad) as cerradas',
            'users.email as email',
            'users.id as id',
            'users.nombre as nombre',
            'fotos_colaboradores.url_foto as url_foto',
            'detalle_colaborador.puesto as puesto'
        );
        $top_3 = DB::table('users')
            ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
            ->join('oportunidades','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
            ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
            ->wherenull('detalle_colaborador.deleted_at')
            ->wherenull('users.deleted_at')
            ->wherenull('fotos_colaboradores.deleted_at')
            ->wherenull('colaborador_oportunidad.deleted_at')
            ->wherenull('oportunidades.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=','2')
            ->selectRaw(implode(',', $selects))
            ->groupBy('users.id')
            ->orderBy('cerradas','desc')
            ->limit(3)
            ->get();
            ;

        $selects = array(
            'users.id as id_colaborador',
            'CONCAT(users.nombre," ",users.apellido) as colaborador',
            'COUNT(colaborador_oportunidad.id_colaborador_oportunidad) AS asignados'  
        );

        $users = DB::table('users')
            ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_colaborador','users.id')
            ->join('fotos_colaboradores','users.id','fotos_colaboradores.id_colaborador')
            ->join('detalle_colaborador','users.id','detalle_colaborador.id_colaborador')
            ->whereNull('users.deleted_at')
            ->whereNull('fotos_colaboradores.deleted_at')
            ->wherenull('colaborador_oportunidad.deleted_at')
            ->wherenull('detalle_colaborador.deleted_at')
            ->select('users.id', 'fotos_colaboradores.url_foto as foto', 'users.email as email', 'detalle_colaborador.whatsapp as telefono')
            ->groupBy('users.id')
            ->get();

        $colaboradores = array();

        foreach($users as $user)
        {
            $oportunidades_asignadas = DB::table('oportunidades')
                ->join('colaborador_oportunidad','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                ->whereNull('oportunidades.deleted_at')
                ->whereNull('colaborador_oportunidad.deleted_at')
                ->whereNull('users.deleted_at')
                ->where('users.id','=',$user->id)
                ->selectRaw(implode(',', $selects))
                ->groupBy('users.id')
                ->first();

            $oportunidades_cotizadas = $this->grafica_por_status(1,$user->id);
            $oportunidades_cerradas = $this->grafica_por_status(2,$user->id);
            $oportunidades_no_viables = $this->grafica_por_status(3,$user->id);
            
            array_push($colaboradores,['colaborador_id' => $oportunidades_asignadas->id_colaborador,
                'colaborador_foto' => $user->foto,
                'colaborador_nombre' => $oportunidades_asignadas->colaborador,
                'oportunidades_asignadas' => ($oportunidades_asignadas) ? $oportunidades_asignadas->asignados : 0,
                'cotizados' => ($oportunidades_cotizadas) ? $oportunidades_cotizadas->asignados : 0,
                'cerrados' => ($oportunidades_cerradas) ? $oportunidades_cerradas->asignados : 0,
                'no_viables' => ($oportunidades_no_viables) ? $oportunidades_no_viables->asignados : 0,
                'total_por_cerrar' => ($oportunidades_cotizadas) ? $oportunidades_cotizadas->valor : 0,
                'total_cerrado' => ($oportunidades_cerradas) ? $oportunidades_cerradas->valor : 0,
                'colaborador_correo' => $user->email,
                'colaborador_telefono' => $user->telefono            
            ]);
        }

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
        
        $total_cotizado = $this->valor_oportunidades_por_status(1);

        $total_cerrador = $this->valor_oportunidades_por_status(2);

        $total_noviable = $this->valor_oportunidades_por_status(3);

        $top_3 = DB::table('users')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
                    ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
                    ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
                    ->wherenull('users.deleted_at')
                    ->wherenull('colaborador_oportunidad.deleted_at')
                    ->wherenull('detalle_colaborador.deleted_at')
                    ->wherenull('detalle_oportunidad.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('fotos_colaboradores.deleted_at')
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
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('detalle_oportunidad.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
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
      $inicioSemana = Carbon::now()->startOfWeek();
      $finSemana = Carbon::now()->endOfWeek();  
      $semana = new Carbon('last week');
      $hoy = new Carbon('now');

        $total_cotizado = $this->valor_oportunidades_por_periodo_por_status($inicioSemana, $finSemana, 1);

        $total_cerrador = $this->valor_oportunidades_por_periodo_por_status($inicioSemana, $finSemana, 2);

        $total_noviable = $this->valor_oportunidades_por_periodo_por_status($inicioSemana, $finSemana, 3);

        $top_3 = $this->valor_top_3_por_periodo($inicioSemana, $finSemana);

        $fuentes = $this->valor_fuentes_por_periodo($inicioSemana, $finSemana);

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
      $inicioMes = Carbon::now()->startOfMonth();
      $finMes = Carbon::now()->endOfMonth();
      $mes = new Carbon('last month');
      $hoy = new Carbon('now');

      $total_cotizado = $this->valor_oportunidades_por_periodo_por_status($inicioMes, $finMes, 1);

      $total_cerrador = $this->valor_oportunidades_por_periodo_por_status($inicioMes, $finMes, 2);

      $total_noviable = $this->valor_oportunidades_por_periodo_por_status($inicioMes, $finMes, 3);

      $top_3 = $this->valor_top_3_por_periodo($inicioMes, $finMes);

      $fuentes = $this->valor_fuentes_por_periodo($inicioMes, $finMes);

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
      $inicioAnio = Carbon::now()->startOfYear();
      $finAnio = Carbon::now()->endOfYear();
      $anio = new Carbon('last year');
      $hoy = new Carbon('now');

        $total_cotizado = $this->valor_oportunidades_por_periodo_por_status($inicioAnio, $finAnio, 1);

        $total_cerrador = $this->valor_oportunidades_por_periodo_por_status($inicioAnio, $finAnio, 2);

        $total_noviable = $this->valor_oportunidades_por_periodo_por_status($inicioAnio, $finAnio, 3);

        $top_3 = $this->valor_top_3_por_periodo($inicioAnio, $finAnio);

        $fuentes = $this->valor_fuentes_por_periodo($inicioAnio, $finAnio);

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
                        ->wherenull('users.deleted_at')
                        ->wherenull('detalle_colaborador.deleted_at')
                        ->wherenull('fotos.colaboradores.deleted_at')
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
        ->where('status', 1)
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
    public function serviciosAjustes(){
        $servicios = DB::table('cat_servicios')
        // ->where('status', 1)
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
                Bugsnag::notifyException(new RuntimeException("No se pudo crear una etiqueta"));
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
            Bugsnag::notifyException(new RuntimeException("No se pudo editar una etiqueta"));
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
            Bugsnag::notifyException(new RuntimeException("No se pudo eliminar una etiqueta"));
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
                Bugsnag::notifyException(new RuntimeException("No se pudo crear un servicio"));
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
            Bugsnag::notifyException(new RuntimeException("No se pudo editar un servicio"));
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
            DB::rollBack();
            Bugsnag::notifyException(new RuntimeException("No se pudo borrar un servicio"));  
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
            Bugsnag::notifyException(new RuntimeException("No se pudo actualizar el status de una oportunidad"));  
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
                      ->wherenull('mediocontacto_catalogo.deleted_at')
                      ->groupBy('medio_contacto_prospectos.id_mediocontacto_catalogo')
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
                                                  ->wherenull('mediocontacto_catalogo.deleted_at')
                                                  ->groupBy('medio_contacto_oportunidades.id_mediocontacto_catalogo')
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
      $auth = $this->guard()->user();
      $data = $request->all();
      $validator = $this->validatorMail($data);

      if ($validator->passes()) {


        if($request->id_prospecto){

            DB::beginTransaction();
            $prospecto = Prospecto::where('id_prospecto',$request->id_prospecto)->first();
            
            
            $medio_contacto = new MedioContactoProspecto;
            $medio_contacto->id_mediocontacto_catalogo = 4;
            $medio_contacto->id_prospecto = $request->id_prospecto;
            $medio_contacto->descripcion = 'Se envió correo desde Kiper.';
            $medio_contacto->fecha = Carbon::now();
            $medio_contacto->hora = Carbon::parse(Carbon::now())->format('H:i');
            
            $statusProspecto = StatusProspecto::where('id_prospecto',$request->id_prospecto)->first();
            $statusProspecto->id_cat_status_prospecto = 1;
            $statusProspecto->save();
            $medio_contacto->save();

            DB::commit();
        }



        Mailgun::send('mailing.mail', $data, function ($message) use ($data){
           // $message->tag('myTag');
           $message->from($data['email_de'],$data['nombre_de']);
           // $message->testmode(true);
           $message->subject($data['asunto']);
           $message->to($data['email_para'],$data['nombre_para']);
       });

       //Historial
        activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Envió','color'=>'#7ac5ff'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un correo a :subject.nombre :subject.apellido </span>');
                
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

    public function cambioStatusProspecto ($id, Request $request){
        $prospecto = Prospecto::where('id_prospecto',$id)->first();
        $auth = $this->guard()->user();
      try {
        DB::beginTransaction();
        $statusProspecto = StatusProspecto::where('id_prospecto',$id)->first();
        $statusProspecto->id_cat_status_prospecto = intval($request->status);
        $statusProspecto->save();
        DB::commit();

        activity('prospecto')
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Cambió','color'=>'#7ac5ff'])
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> Cambió de status a :subject.nombre :subject.apellido </span>');
                

        return response()->json([
            'error'=>false,
            'message'=>'El status ha cambiado.'
        ],200);
      }catch (Exception $e) {
        DB::rollBack();
        Bugsnag::notifyException(new RuntimeException("No se pudo cambiar el status de un prospecto"));  
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
      $auth = $this->guard()->user();
      $validator = $this->validatorMedioContactoProspecto($request->all());
      $colaborador = $this->guard()->user();
      $prospecto = Prospecto::where('id_prospecto',$request->id_prospecto)->first();
      if ($validator->passes()) {
        try {
          DB::beginTransaction();
          
          $medio_contacto_prospecto = new MedioContactoProspecto;
          $medio_contacto_prospecto->id_mediocontacto_catalogo = $request->id_mediocontacto_catalogo;
          $medio_contacto_prospecto->id_prospecto = $request->id_prospecto;
          $medio_contacto_prospecto->descripcion = $request->descripcion;
          $medio_contacto_prospecto->fecha = $request->fecha;
          $medio_contacto_prospecto->hora = $request->hora;
          $medio_contacto_prospecto->lugar = $request->lugar;
          $medio_contacto_prospecto->save();

          $status = StatusProspecto::where('id_prospecto',$request->id_prospecto)->first();
          $status->id_cat_status_prospecto = 1;
          $status->save();
          DB::commit();
          
         
          
          $details_medio = MedioContactoProspecto::with('medio_contacto')->where('id_medio_contacto_prospecto',$medio_contacto_prospecto->id_medio_contacto_prospecto)->first();
          //Historial
          
                
            activity('prospecto')
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>$details_medio->medio_contacto->nombre,'color'=>$details_medio->medio_contacto->color])
                ->log(':causer.nombre :causer.apellido<br> <span class="histroial_status"> Contactó vía :properties.accion a :subject.nombre :subject.apellido </span>');
          

          
            if($medio_contacto_prospecto->id_mediocontacto_catalogo == 5){
                
                $evento = new EventoProspecto;
                $evento->id_colaborador = $colaborador->id;
                $evento->id_prospecto = $request->id_prospecto;
                $evento->save();
                $detalle_evento = new DetalleEventoProspecto;
                $detalle_evento->id_evento_prospecto = $evento->id_evento_prospecto;
                $detalle_evento->fecha_evento = $request->fecha;
                $detalle_evento->hora_evento = $request->hora;
                $detalle_evento->nota_evento = $request->descripcion;
                $detalle_evento->lugar_evento = $request->lugar;
                $evento->detalle()->save($detalle_evento);
                
                $time = Carbon::parse($detalle_evento->fecha_evento);
                //return response()->json($time);
                $link = Link::create('Evento Kiper', $time,$time)
                        ->description($detalle_evento->nota_evento)
                        ->address($detalle_evento->lugar_evento);
                
                return response()->json([
                    'error'=>false,
                    'message'=>'Medio de contacto agregago exitósamente.',
                    'links'=>['google'=>$link->google(),
                                          'outlook'=>$link->webOutlook(),
                                          'ics'=>$link->ics()]
                 ],200);

            }else{
                
               return response()->json([
                    'error'=>false,
                    'message'=>'Medio de contacto agregago exitósamente.',
                ],200);

            }


          

        } catch (Exception $e) {
          DB::rollBack();
          Bugsnag::notifyException(new RuntimeException("No se pudo crear un seguimiento en Prospecto"));  
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
          Bugsnag::notifyException(new RuntimeException("No se pudo crear un seguimiento en Oportunidad"));
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

    public function grafica_por_status($status,$id){
        $selects = array(
            'SUM(detalle_oportunidad.valor) AS valor',
            'COUNT(colaborador_oportunidad.id_colaborador_oportunidad) AS asignados'
        );
        return DB::table('oportunidades')
                ->join('colaborador_oportunidad','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                ->whereNull('oportunidades.deleted_at')
                ->whereNull('colaborador_oportunidad.deleted_at')
                ->whereNull('users.deleted_at')
                ->whereNull('detalle_oportunidad.deleted_at')
                ->whereNull('status_oportunidad.deleted_at')
                ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
                ->where('users.id','=',$id)
                ->selectRaw(implode(',', $selects))
                ->groupBy('users.id')
                ->first();
    }
    public function dashboard_colaboradores_periodo($inicio, $fin){
        $selects = array(
            'users.nombre as nombre',
            'users.apellido as apellido',
            'users.id as id',
            'detalle_colaborador.puesto as puesto',
            'fotos_colaboradores.url_foto as url_foto',
            'count(colaborador_oportunidad.id_colaborador_oportunidad) as oportunidades_cerradas'
        );
        return $users = DB::table('users')
            ->join('detalle_colaborador', 'users.id', 'detalle_colaborador.id_colaborador')
            ->join('colaborador_oportunidad','users.id','colaborador_oportunidad.id_colaborador')
            ->join('fotos_colaboradores', 'users.id', 'fotos_colaboradores.id_colaborador')
            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad', 'status_oportunidad.id_oportunidad')
            ->join('oportunidades','oportunidades.id_oportunidad', 'colaborador_oportunidad.id_oportunidad')
            ->wherenull('oportunidades.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->wherenull('users.deleted_at')
            ->wherenull('detalle_colaborador.deleted_at')
            ->wherenull('colaborador_oportunidad.deleted_at')
            ->wherenull('fotos_colaboradores.deleted_at')
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->where('status_oportunidad.id_cat_status_oportunidad', '=', '2')
            ->selectRaw(implode(',', $selects))
            ->groupBy('users.id')
            ->orderBy('oportunidades_cerradas','desc')
            ->limit(5)
            ->get();
    }

    public function oportunidades_por_periodo_por_status($inicio, $fin, $status)
    {
        return DB::table('oportunidades')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->whereNull('oportunidades.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();
    }

    public function prospectos_por_periodo_por_status($inicio, $fin, $status)
    {
        return DB::table('prospectos')
            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->wherenull('status_prospecto.deleted_at')
            ->whereBetween('status_prospecto.updated_at', array($inicio ,$fin))
            ->where('status_prospecto.id_cat_status_prospecto','=',$status)->count();
    }

    public function ingresos_por_periodo_por_status($inicio, $fin, $status)
    {
        return DB::table('oportunidades')
            ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
            ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->wherenull('oportunidades.deleted_at')
            ->wherenull('detalle_oportunidad.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->where('status_oportunidad.id_cat_status_oportunidad',$status)
            ->sum('detalle_oportunidad.valor');
            
    }

    public function origen_por_periodo($inicio, $fin)
    {
        return DB::table('prospectos')
            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
            ->wherenull('prospectos.deleted_at')
            ->wherenull('cat_fuentes.deleted_at')
            ->where('prospectos.deleted_at',null)
            ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
            ->whereBetween('prospectos.updated_at', array($inicio ,$fin))
            ->groupBy('cat_fuentes.nombre')->get();
    }

    public function oportunidades_por_colaborador_por_status($id,$status){
        return DB::table('oportunidades')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('oportunidades.deleted_at')
            ->where('colaborador_oportunidad.id_colaborador','=',$id)
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();
    }

    public function oportunidades_por_status($status){
        return DB::table('oportunidades')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('oportunidades.deleted_at')
            ->whereNull('colaborador_oportunidad.deleted_at')
            ->whereNull('status_oportunidad.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();
    }

    public function valor_oportunidades_por_status($status){
        return DB::table('oportunidades')
            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('oportunidades.deleted_at')
            ->wherenull('detalle_oportunidad.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
            ->sum('detalle_oportunidad.valor');
    }

    public function valor_oportunidades_por_periodo_por_status($inicio, $fin, $status){
        return DB::table('oportunidades')
            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('detalle_oportunidad.deleted_at')
            ->whereNull('status_oportunidad.deleted_at')
            ->whereNull('oportunidades.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
            ->whereBetween('detalle_oportunidad.created_at', array($inicio ,$fin))
            ->sum('detalle_oportunidad.valor');
    }

    public function valor_top_3_por_periodo($inicio, $fin){
        return DB::table('users')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_colaborador','users.id')
            ->join('detalle_colaborador','detalle_colaborador.id_colaborador','users.id')
            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
            ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
            ->join('fotos_colaboradores','fotos_colaboradores.id_colaborador','users.id')
            ->join('oportunidades', 'colaborador_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
            ->wherenull('users.deleted_at')
            ->wherenull('colaborador_oportunidad.deleted_at')
            ->wherenull('detalle_colaborador.deleted_at')
            ->wherenull('detalle_oportunidad.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->wherenull('fotos_colaboradores.deleted_at')
            ->wherenull('oportunidades.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',2)
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->select('users.id','users.nombre','users.apellido','fotos_colaboradores.url_foto','detalle_colaborador.puesto',DB::raw('sum(detalle_oportunidad.valor) as total_ingresos'))
            ->groupBy('users.id')
            ->orderBy('total_ingresos','desc')
            ->limit(3)
            ->get();
    }

    public function valor_fuentes_por_periodo($inicio, $fin){
        return DB::table('oportunidades')
            ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
            ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','oportunidades.id_oportunidad')
            ->join('prospectos','prospectos.id_prospecto','oportunidad_prospecto.id_prospecto')
            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
            ->wherenull('oportunidades.deleted_at')
            ->wherenull('detalle_oportunidad.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->wherenull('oportunidad_prospecto.deleted_at')
            ->wherenull('prospectos.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad',2)
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->select(DB::raw('SUM(detalle_oportunidad.valor) as total'),'cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')
            ->get();
    }
}

<?php

namespace App\Http\Controllers\Estadisticas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
use App\Modelos\Prospecto\CatStatusProspecto;
use App\Modelos\Prospecto\MedioContactoProspecto;
use App\Modelos\Prospecto\ColaboradorProspecto;
use App\Modelos\Oportunidad\MedioContactoOportunidad;
use App\Modelos\Extras\EventoProspecto;
use App\Modelos\Extras\DetalleEventoProspecto;
use App\Modelos\Extras\RecordatorioProspecto;
use App\Modelos\Extras\DetalleRecordatorioProspecto;

use Mailgun;
use DB;
use Mail;

class EstadisticasController extends Controller
{
    public function estadisticas_oportunidad(){
       

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

        $catalogo_status = DB::table('cat_status_oportunidad')
                    ->select('id_cat_status_oportunidad as id','status','color')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();


        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'status'=>$this->StatusChecker($catalogo_status,$this->oportunidades_status_genericos('0')),
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
            ]
            ],200);

    }

    public function estadisticas_oportunidad_personal(){
        $id = $this->guard()->user()->id;

        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->whereNull('oportunidades.deleted_at')
                            ->whereNull('colaborador_oportunidad.deleted_at')
                            ->whereNull('oportunidad_prospecto.deleted_at')
                            ->whereNull('prospectos.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id)
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')->get(); 
        
        $catalogo_status = CatStatusOportunidad::all();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();


        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
               
                'status'=>$this->StatusChecker($catalogo_status,$this->oportunidades_status_genericos($id)),
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
            ]
            ],200);
                           
    }
    public function estadisticas_oportunidad_personal_por_fecha($inicio, $fin){
        $inicioPeriodo = new Carbon($inicio);
        $finPeriodo = new Carbon(($fin));
        $id = $this->guard()->user()->id;

        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->whereNull('oportunidades.deleted_at')
                            ->whereNull('colaborador_oportunidad.deleted_at')
                            ->whereNull('oportunidad_prospecto.deleted_at')
                            ->whereNull('prospectos.deleted_at')
                            ->where('colaborador_oportunidad.id_colaborador',$id)
                            ->whereBetween('colaborador_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')->get(); 
        
        $catalogo_status = DB::table('cat_status_oportunidad')
                    ->select('id_cat_status_oportunidad as id','status','color')
                    ->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();


        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
               
                'status'=>$this->StatusChecker($catalogo_status,$this->oportunidades_status_genericos($id, $inicioPeriodo, $finPeriodo)),
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
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
            ->limit(5)
            ->get();
            ;

        $selects = array(
            'users.id as id_colaborador',
            'users.nombre as colaborador',
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
        
        $status = CatStatusOportunidad::all();
        
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
            $total_estado = Array();
            foreach($status as $estado)
            {
                $aux = $this->grafica_por_status($estado->id_cat_status_oportunidad, $user->id);
                array_push($total_estado, ($aux) ? $aux->asignados : 0);
            }
            
            array_push($colaboradores,['colaborador_id' => $oportunidades_asignadas->id_colaborador,
                'colaborador_foto' => $user->foto,
                'colaborador_nombre' => $oportunidades_asignadas->colaborador,
                'oportunidades_asignadas' => ($oportunidades_asignadas) ? $oportunidades_asignadas->asignados : 0,
                'total_por_cerrar' => ($oportunidades_cotizadas) ? $oportunidades_cotizadas->valor : 0,
                'total_cerrado' => ($oportunidades_cerradas) ? $oportunidades_cerradas->valor : 0,
                'colaborador_correo' => $user->email,
                'colaborador_telefono' => $user->telefono,
                'total_estado' => $total_estado
            ]);
        }

        $status = CatStatusOportunidad::all();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'ventas'=>$users_ventas,
                'top_3'=>$top_3,
                'colaboradores'=>$colaboradores,
                'status' => $status
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
                    ->limit(5)
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

        $total_cotizado = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 1);

        $total_cerrador = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 2);

        $total_noviable = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 3);

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

      $total_cotizado = $this->ingresos_por_periodo_por_status($inicioMes, $finMes, 1);

      $total_cerrador = $this->ingresos_por_periodo_por_status($inicioMes, $finMes, 2);

      $total_noviable = $this->ingresos_por_periodo_por_status($inicioMes, $finMes, 3);

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

    public function oportunidades_por_status($status){
        return DB::table('oportunidades')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('oportunidades.deleted_at')
            ->whereNull('colaborador_oportunidad.deleted_at')
            ->whereNull('status_oportunidad.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();
    }

    public function valor_oportunidades_por_periodo_por_status($inicio, $fin, $status){
        return DB::table('oportunidades')
            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereNull('detalle_oportunidad.deleted_at')
            ->whereNull('status_oportunidad.deleted_at')
            ->whereNull('oportunidades.deleted_at')
            ->where('status_oportunidad.id_cat_status_oportunidad','=',$status)
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
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
            ->limit(5)
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

    public function StatusChecker($catalogo,$consulta){

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

                            if( $catalogo[$i]->status == $consulta[$j]->status ){
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

    public function oportunidades_status_genericos($id){
        if($id == '0'){

            return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get();
        }else{
            
            return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->where('colaborador_oportunidad.id_colaborador',$id)
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get();
        }
        
            
    }

    public function oportunidades_status_genericos_por_fecha($id, $inicio, $fin){
        return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->where('colaborador_oportunidad.id_colaborador',$id)
                    ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get();       
    }

    public function guard()
    {
        return Auth::guard();
    }



}
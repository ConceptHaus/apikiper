<?php

namespace App\Http\Controllers\DataViews;

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
use App\Events\Historial;
use App\Events\Event;
use App\Events\AssignProspecto;
use App\Modelos\Empresa\EmpresaProspecto;
use App\Http\Enums\Permissions;
use Mailgun;
use DB;
use Mail;

use App\Http\Services\Roles\RolesService;


class DataViewsController extends Controller
{

    public function dashboardPorFecha($inicio, $fin){
        $auth = $this->guard()->user();
        //return $inicio.' '.$fin;
        //Oportunidades Cotizadas
        //Oportunidades Cerradas
        //Prospectos sin contactar
        //Colaboradores
        //Ingresos
        //Origen Prospecto
        //Historial
        $inicioSemana = (new Carbon($inicio))->addDays(-1);
        $finSemana = (new Carbon(($fin)))->addDays(1);
        
        $oportuniades_cerradas = $this->oportunidades_por_periodo_por_status($inicioSemana,$finSemana,2,$auth);
        $oportunidades_cotizadas = $this->oportunidades_por_periodo_por_status($inicioSemana,$finSemana,1,$auth);
        $colaboradores = $this->dashboard_colaboradores_periodo($inicioSemana,$finSemana); //
        $prospectos_sin_contactar = $this->prospectos_sin_contactar($auth);
        $ingresos = $this->ingresos_por_periodo_por_status($inicioSemana,$finSemana,2, $auth);
        $origen = $this->origen_por_periodo($inicioSemana, $finSemana, $auth);

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


    public function prospectos(){
        
        $permisos = User::getAuthenticatedUserPermissions();
        
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

        $etiquetas = DB::table('etiquetas')->select('*')->get();
        $auth = $this->guard()->user();  
        if($auth->rol == 1){
            $prospectos = Prospecto::with('detalle_prospecto')
                            ->with('colaborador_prospecto.colaborador.detalle')
                            ->with('fuente')
                            ->with('status_prospecto.status')
                            ->with('prospectos_empresas')
                            ->with('prospectos_empresas.empresas')
                            ->with('etiquetas_prospecto')
                            ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                            ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                            ->where('etiquetas.nombre','like','%polanco%')
                            ->groupby('prospectos.id_prospecto')
                            ->orderBy('prospectos.created_at','desc')
                            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                            ->get();
            $total_prospectos = $prospectos->count();
            $origen = DB::table('prospectos')
                    ->distinct()
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                    ->wherenull('prospectos.deleted_at')
                    ->where('etiquetas.nombre','like','%polanco%')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status', DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')
                    ->get();
                
            $nocontactados_prospectos = DB::table('prospectos')
                    ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                    ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                    ->wherenull('prospectos.deleted_at')
                    ->where('etiquetas.nombre','like','%polanco%')
                    ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
        }
        else if($auth->rol == 2){
            $prospectos = Prospecto::with('detalle_prospecto')
                            ->with('colaborador_prospecto.colaborador.detalle')
                            ->with('fuente')
                            ->with('prospectos_empresas')
                            ->with('prospectos_empresas.empresas')
                            ->with('status_prospecto.status')
                            ->with('etiquetas_prospecto')
                            ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                            ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                            ->where('etiquetas.nombre','like','%napoles%')
                            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                            ->orderBy('prospectos.created_at','desc')
                            ->groupby('prospectos.id_prospecto')
                            ->get();
            $total_prospectos = $prospectos->count();
            
            $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                    ->wherenull('prospectos.deleted_at')
                    ->where('etiquetas.nombre','like','%napoles%')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(DISTINCT(prospectos.id_prospecto)) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();
                
            $nocontactados_prospectos = DB::table('prospectos')
                    ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                    ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                    ->wherenull('prospectos.deleted_at')
                    ->where('etiquetas.nombre','like','%napoles%')
                    ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
        }
        else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
                $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('colaborador_prospecto.colaborador.detalle')
                                ->with('fuente')
                                /*->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->join('users', 'users.id', 'colaborador_prospecto.id_prospecto')
                                ->wherenull('users.deleted_at')
                                ->wherenull('colaborador_prospecto.deleted_at')*/
                                ->wherenull('prospectos.deleted_at')
                                ->with('status_prospecto.status')
                                ->with('prospectos_empresas')
                                ->with('etiquetas_prospecto')
                                ->with('prospectos_empresas.empresas')
                                ->orderBy('prospectos.created_at','desc')
                                //->groupBy('prospectos.id_prospecto')
                                ->get();
        }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
                $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('colaborador_prospecto.colaborador.detalle')
                                ->with('fuente')
                                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->where('colaborador_prospecto.id_colaborador',$auth->id)
                                ->wherenull('colaborador_prospecto.deleted_at')
                                ->wherenull('prospectos.deleted_at')
                                ->with('status_prospecto.status')
                                ->with('prospectos_empresas')
                                ->with('etiquetas_prospecto')
                                ->with('prospectos_empresas.empresas')
                                ->orderBy('prospectos.created_at','desc')
                                //->groupBy('prospectos.id_prospecto')
                                ->get();
                
                $total_prospectos = Prospecto::join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                                    ->where('colaborador_prospecto.id_colaborador',$auth->id)->count();
                
                $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->where('colaborador_prospecto.id_colaborador',$auth->id)
                    ->wherenull('prospectos.deleted_at')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();
                
                $nocontactados_prospectos = DB::table('prospectos')
                                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                                ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->where('colaborador_prospecto.id_colaborador',$auth->id)
                                ->wherenull('prospectos.deleted_at')
                                ->wherenull('prospectos.deleted_at')
                                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
        }else{
            $prospectos                 = [];
            $total_prospectos           = [];
            $origen                     = [];
            $nocontactados_prospectos   = [];    
        }
        
        $colaboradores = User::all();
        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->wherenull('cat_fuentes.deleted_at')
                            ->select('nombre','url','status')->get();
        
        $prospectos_status = CatStatusProspecto::get();
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'prospectos'=>$prospectos,
                'prospectos_total'=>$total_prospectos,
                'prospectos_nocontactados'=> $nocontactados_prospectos,
                'prospectos_fuente'=>$this->FuentesChecker($catalogo_fuentes,$origen),
                'prospectos_status'=> $prospectos_status,
                'colaboradores'=> $colaboradores,
                'etiquetas'=>$etiquetas
            ]
            ],200);
    }
    
    public function prospectosstatus($status){
        
        $permisos = User::getAuthenticatedUserPermissions();
        
        $auth = $this->guard()->user();
        $prospectos_status = CatStatusProspecto::get();
        $colaboradores = User::all();
        $etiquetas = DB::table('etiquetas')->select('*')->get();
        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->wherenull('cat_fuentes.deleted_at')
                            ->select('nombre','url','status')->get();
        $origen = DB::table('prospectos')
                    ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                    ->wherenull('prospectos.deleted_at')
                    ->select('cat_fuentes.nombre','cat_fuentes.url','cat_fuentes.status',DB::raw('count(*) as total, cat_fuentes.nombre'))
                    ->groupBy('cat_fuentes.nombre')->get();
        if($auth->rol == 1){
            $prospectos = Prospecto::with('detalle_prospecto')
                            ->with('colaborador_prospecto.colaborador.detalle')
                            ->with('fuente')
                            ->with('status_prospecto.status')
                            ->with('prospectos_empresas')
                            ->with('prospectos_empresas.empresas')
                            ->with('etiquetas_prospecto')
                            ->leftjoin('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                            ->leftjoin('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                            ->where('etiquetas.nombre','like','%polanco%')
                            ->where('status_prospecto.id_cat_status_prospecto',$status)
                            ->groupby('prospectos.id_prospecto')
                            ->orderBy('prospectos.created_at','desc')
                            ->select('*','prospectos.created_at','prospectos.nombre','etiquetas.nombre AS nombre_etiqueta')
                            ->get();
        }
        else if($auth->rol == 2){
            $prospectos = DB::table('prospectos')   
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->join('etiquetas_prospectos','etiquetas_prospectos.id_prospecto','prospectos.id_prospecto')
                        ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_prospectos.id_etiqueta')
                        ->where('etiquetas.nombre','like','%napoles%')
                        ->leftJoin('prospectos_empresas', 'prospectos.id_prospecto', '=', 'prospectos_empresas.id_prospecto')
                        ->leftJoin('empresas', 'prospectos_empresas.id_empresa', '=', 'empresas.id_empresa')
                        //->join('empresas', 'prospectos_empresas.id_empresa', 'empresas.id_empresa')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('detalle_prospecto.deleted_at')
                        ->whereNull('status_prospecto.deleted_at')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono', 'empresas.id_empresa','empresas.nombre as empresa', 'detalle_prospecto.empresa as empresa2','detalle_prospecto.whatsapp','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status', 'cat_status_prospecto.color as color')
                        ->orderBy('status_prospecto.updated_at','desc')
                        ->groupBy('prospectos.id_prospecto')
                        ->get();
        }
        else if(in_array(Permissions::PROSPECTS_READ_ALL, $permisos)){
            
            $prospectos = Prospecto::with('detalle_prospecto')
                                ->with('colaborador_prospecto.colaborador.detalle')
                                ->with('fuente')
                                /*->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                                ->join('users', 'users.id', 'colaborador_prospecto.id_prospecto')
                                ->wherenull('users.deleted_at')
                                ->wherenull('colaborador_prospecto.deleted_at')*/
                                ->wherenull('prospectos.deleted_at')
                                ->with('status_prospecto.status')
                                ->with('prospectos_empresas')
                                ->with('etiquetas_prospecto')
                                ->with('prospectos_empresas.empresas')
                                ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                                ->where('status_prospecto.id_cat_status_prospecto',$status)
                                ->orderBy('prospectos.created_at','desc')
                                //->groupBy('prospectos.id_prospecto')
                                ->get();
        }else if(in_array(Permissions::PROSPECTS_READ_OWN, $permisos)){
            $prospectos = DB::table('prospectos')
                        ->join('detalle_prospecto','detalle_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('status_prospecto','status_prospecto.id_prospecto','prospectos.id_prospecto')
                        ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                        ->join('cat_status_prospecto','cat_status_prospecto.id_cat_status_prospecto','status_prospecto.id_cat_status_prospecto')
                        ->join('colaborador_prospecto', 'colaborador_prospecto.id_prospecto', 'prospectos.id_prospecto')
                        ->where('colaborador_prospecto.id_colaborador',$auth->id)
                        ->leftJoin('prospectos_empresas', 'prospectos.id_prospecto', '=', 'prospectos_empresas.id_prospecto')
                        ->leftJoin('empresas', 'prospectos_empresas.id_empresa', '=', 'empresas.id_empresa')
                        //->join('empresas', 'prospectos_empresas.id_empresa', 'empresas.id_empresa')
                        ->whereNull('prospectos.deleted_at')
                        ->whereNull('detalle_prospecto.deleted_at')
                        ->whereNull('status_prospecto.deleted_at')
                        ->where('status_prospecto.id_cat_status_prospecto',$status)
                        ->select('prospectos.id_prospecto','prospectos.nombre','prospectos.apellido','prospectos.correo','detalle_prospecto.telefono', 'empresas.id_empresa','empresas.nombre as empresa', 'detalle_prospecto.empresa as empresa2','detalle_prospecto.whatsapp','prospectos.created_at','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','cat_status_prospecto.status','cat_status_prospecto.id_cat_status_prospecto as id_status', 'cat_status_prospecto.color as color')
                        ->orderBy('status_prospecto.updated_at','desc')
                        ->groupBy('prospectos.id_prospecto')
                        ->get();
        }else{
            $prospectos                 = [];
            $total_prospectos           = [];
            $origen                     = [];
            $nocontactados_prospectos   = [];    
        }
        
        foreach($prospectos as $p){
            $ep = EmpresaProspecto::where('id_prospecto', '=', $p->id_prospecto)->with('empresas')->get();
            $p->empresa = [];
            $p->id_empresa = [];
            $aux = [];
            $aux2 = [];
            foreach($ep as $_ep){
                array_push($aux, $_ep->empresas->nombre);
                array_push($aux2, $_ep->empresas->id_empresa);
            }
            $p->empresa = $aux;
            $p->id_empresa = $aux2;
        }
        
        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'prospectos'=>$prospectos,
                'prospectos_fuente'=>$this->FuentesChecker($catalogo_fuentes,$origen),
                'prospectos_status'=> $prospectos_status,
                'colaboradores'=> $colaboradores,
                'etiquetas'=>$etiquetas
            ]
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

        $valor_cotizadas = DB::table('oportunidades')
                            ->join('detalle_oportunidad','detalle_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('status_oportunidad','detalle_oportunidad.id_oportunidad','status_oportunidad.id_oportunidad')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->where('status_oportunidad.id_cat_status_oportunidad','=',1, )
                            ->where('colaborador_oportunidad.id_colaborador',$id)
                            ->groupBy('status_oportunidad.id_cat_status_oportunidad')
                            ->sum(DB::raw('detalle_oportunidad.valor * detalle_oportunidad.meses'));
                            

        $oportunidades_cerradas = $this->oportunidades_por_colaborador_por_status($id,2);

        $oportunidades_no_viables = $this->oportunidades_por_colaborador_por_status($id,3);

        $s_o = CatStatusOportunidad::all(); 
        
        $oportunidades_status = Array();
        $oportunidades_status_p = Array();
        foreach( $s_o as $status)
        {
            $total_status = $this->oportunidades_por_colaborador_por_status($id,$status->id_cat_status_oportunidad);
            $porcentaje_status = $this->porcentajeOportunidades($total_status,$oportunidades_total);

            array_push($oportunidades_status, $total_status);
            array_push($oportunidades_status_p, $porcentaje_status);
        }
       
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
                            ->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','detalle_oportunidad.meses','cat_status_oportunidad.status','cat_status_oportunidad.color as color_status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.nombre as fuente_url','oportunidades.created_at','users.id as id_colaborador', 'users.nombre as asignado_nombre', 'users.apellido as asignado_apellido')
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
                    'color'=>$this->colorsOportunidades(1),
                    'valorPorStatus' => $valor_cotizadas

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
                'oportunidades'=>$oportunidades,

                'oportunidades_status' => $oportunidades_status,
                'porcentaje_status' => $oportunidades_status_p
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
                            ->wherenull('servicio_oportunidad.deleted_at')->select('colaborador_oportunidad.id_oportunidad','oportunidades.nombre_oportunidad','detalle_oportunidad.valor','detalle_oportunidad.meses','cat_status_oportunidad.status','cat_status_oportunidad.id_cat_status_oportunidad as id_status','cat_status_oportunidad.color as color_status','cat_servicios.nombre as servicio','prospectos.id_prospecto','prospectos.nombre as nombre_prospecto','prospectos.apellido as apellido_prospecto','cat_fuentes.nombre as fuente','cat_fuentes.url as fuente_url','users.id as id_colaborador','users.nombre as asignado_nombre', 'users.apellido as asignado_apellido','oportunidades.created_at')
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

        $status = DB::table('cat_status_oportunidad')
                      ->select('id_cat_status_oportunidad as id','status','color')->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'status'=>$this->StatusChecker($catalogo_status,$this->oportunidades_status_genericos()),
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
            ]
            ],200);
    }

    public function estadisticas_oportunidad_por_fecha($inicio, $fin){
        $inicioPeriodo = (new Carbon($inicio))->addDays(-1);
        $finPeriodo = (new Carbon($fin))->addDays(1);
        $fuentes = DB::table('oportunidades')
                            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                            ->join('oportunidad_prospecto','oportunidad_prospecto.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                            ->join('prospectos','oportunidad_prospecto.id_prospecto','prospectos.id_prospecto')
                            ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                            ->whereNull('oportunidades.deleted_at')
                            ->whereNull('colaborador_oportunidad.deleted_at')
                            ->whereNull('oportunidad_prospecto.deleted_at')
                            ->whereNull('prospectos.deleted_at')
                            ->whereBetween('colaborador_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))
                            ->select(DB::raw('count(*) as total, cat_fuentes.nombre'),'cat_fuentes.url','cat_fuentes.status')->groupBy('cat_fuentes.nombre')->get();    

                            $catalogo_status = CatStatusOportunidad::all();

        $status = DB::table('cat_status_oportunidad')
                      ->select('id_cat_status_oportunidad as id','status','color')->get();

        $catalogo_fuentes = DB::table('cat_fuentes')
                            ->select('nombre','url','status')->get();

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'status'=>$this->StatusChecker($catalogo_status,$this->oportunidades_status_genericos_por_fecha($inicio, $fin)),
                'fuentes'=>$this->FuentesChecker($catalogo_fuentes, $fuentes),
            ]
            ],200);
    }

    public function estadisticas_oportunidad_grafica(Request $request)
    {
        $meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
        $finMes = Carbon::now();
        $inicioMes = Carbon::now();

        $mes_actual = Carbon::now()->month;
        $mes_actual = 12;
        $oportunidad_fuente_mes = array();

        if(isset($request->status))
            $status = $request->status;

        if(isset($request->etiqueta))
            $etiqueta = $request->etiqueta;

        if(isset($request->servicio))
            $servicio = $request->servicio;

        if(isset($status) && isset($etiqueta) && isset($servicio))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->join('etiquetas_oportunidades', 'etiquetas_oportunidades.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_oportunidades.id_etiqueta')
                    ->join('servicio_oportunidad', 'servicio_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->wherenull('etiquetas_oportunidades.deleted_at')
                    ->wherenull('etiquetas.deleted_at')
                    ->wherenull('servicio_oportunidad.deleted_at')
                    ->wherenull('cat_servicios.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('status_oportunidad.id_cat_status_oportunidad', '=', $status)
                    ->where('etiquetas.id_etiqueta', '=', $etiqueta)
                    ->where('cat_servicios.id_servicio_cat', '=', $servicio)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($status) && isset($etiqueta))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->join('etiquetas_oportunidades', 'etiquetas_oportunidades.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_oportunidades.id_etiqueta')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->wherenull('etiquetas_oportunidades.deleted_at')
                    ->wherenull('etiquetas.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('status_oportunidad.id_cat_status_oportunidad', '=', $status)
                    ->where('etiquetas.id_etiqueta', '=', $etiqueta)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($status) && isset($servicio))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->join('servicio_oportunidad', 'servicio_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->wherenull('servicio_oportunidad.deleted_at')
                    ->wherenull('cat_servicios.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('status_oportunidad.id_cat_status_oportunidad', '=', $status)
                    ->where('cat_servicios.id_servicio_cat', '=', $servicio)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($etiqueta) && isset($servicio))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('servicio_oportunidad', 'servicio_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                    ->join('etiquetas_oportunidades', 'etiquetas_oportunidades.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_oportunidades.id_etiqueta')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('servicio_oportunidad.deleted_at')
                    ->wherenull('cat_servicios.deleted_at')
                    ->wherenull('etiquetas_oportunidades.deleted_at')
                    ->wherenull('etiquetas.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('cat_servicios.id_servicio_cat', '=', $servicio)
                    ->where('etiquetas.id_etiqueta', '=', $etiqueta)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($status))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('status_oportunidad.id_cat_status_oportunidad', '=', $status)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($etiqueta))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('etiquetas_oportunidades', 'etiquetas_oportunidades.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('etiquetas','etiquetas.id_etiqueta','etiquetas_oportunidades.id_etiqueta')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('etiquetas_oportunidades.deleted_at')
                    ->wherenull('etiquetas.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('etiquetas.id_etiqueta', '=', $etiqueta)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        elseif(isset($servicio))
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->join('servicio_oportunidad', 'servicio_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->join('cat_servicios','cat_servicios.id_servicio_cat','servicio_oportunidad.id_servicio_cat')
                    ->join('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->wherenull('servicio_oportunidad.deleted_at')
                    ->wherenull('cat_servicios.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at',array($inicioMes, $finMes))
                    ->where('cat_servicios.id_servicio_cat', '=', $servicio)
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }
        else
        {
            for($i = 1; $i <= $mes_actual; $i++)
            {
                $finMes->month = $i;
                $finMes = $finMes->endOfMonth();
                $inicioMes->month = $i;
                $inicioMes = $inicioMes->startOfMonth();
                
                
                $consulta = DB::table('oportunidades')
                    ->join('oportunidad_prospecto','oportunidades.id_oportunidad','oportunidad_prospecto.id_oportunidad')
                    ->join('prospectos', 'oportunidad_prospecto.id_prospecto', 'prospectos.id_prospecto')
                    ->join('cat_fuentes', 'prospectos.fuente','cat_fuentes.id_fuente')
                    ->wherenull('oportunidades.deleted_at')
                    ->wherenull('oportunidad_prospecto.deleted_at')
                    ->wherenull('prospectos.deleted_at')
                    ->whereBetween('oportunidades.updated_at',array($inicioMes, $finMes))
                    ->select('cat_fuentes.id_fuente', 'cat_fuentes.nombre as nombre_fuente', DB::raw('count(*) as cantidad'))
                    ->groupBy('prospectos.fuente')
                    ->get();
                array_push($oportunidad_fuente_mes, $meses[$i-1], $consulta);
            }
        }

        return response()->json([
            'message'=>'Correcto',
            'error'=>false,
            'data'=>[
                'oportunidades_por_fuente'=>$oportunidad_fuente_mes
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

    public function estadisticas_colaborador_por_fecha($inicio, $fin){
        $inicioPeriodo = (new Carbon($inicio))->addDays(-1);
        $finPeriodo = (new Carbon($fin))->addDays(1);
        
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
                        ->whereBetween('status_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))
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
        //return implode(',', $selects);
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
            ->whereBetween('status_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))            
            ->selectRaw(implode(',', $selects))
            //users.apellido as apellido,count(colaborador_oportunidad.id_colaborador_oportunidad) as cerradas,users.email as email,
            //users.id as id,users.nombre as nombre,fotos_colaboradores.url_foto as url_foto,detalle_colaborador.puesto as puesto
            ->groupBy('users.id')
            ->orderBy('cerradas','desc')
            ->limit(5)
            ->get();

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
        
        $status = CatStatusOportunidad::all();
        
        foreach($users as $user)
        {
            $oportunidades_asignadas = DB::table('oportunidades')
                ->join('colaborador_oportunidad','oportunidades.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                ->join('users','colaborador_oportunidad.id_colaborador','users.id')
                ->leftjoin('status_oportunidad', 'status_oportunidad.id_oportunidad', 'oportunidades.id_oportunidad')
                ->whereNull('oportunidades.deleted_at')
                ->whereNull('colaborador_oportunidad.deleted_at')
                ->whereNull('users.deleted_at')
                ->where('users.id','=',$user->id)
                ->whereBetween('status_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))
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
            
            if($oportunidades_asignadas)
            {
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

    public function estadisticas_finanzas_por_fecha($inicio, $fin){
        $auth = $this->guard()->user();  
        $inicioSemana = (new Carbon($inicio))->addDays(-1);
        $finSemana = (new Carbon($fin))->addDays(1);
  
          $total_cotizado = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 1, $auth);
  
          $total_cerrador = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 2, $auth);
  
          $total_noviable = $this->ingresos_por_periodo_por_status($inicioSemana, $finSemana, 3, $auth);
  
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
        
        $catalogo_status = DB::table('cat_status_oportunidad')
                    ->select('id_cat_status_oportunidad as id','status as nombre','color')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->get();
        $catalogo_status_select = DB::table('cat_status_oportunidad')
                    ->join('status_oportunidad', 'status_oportunidad.id_cat_status_oportunidad', 'cat_status_oportunidad.id_cat_status_oportunidad')
                    ->join('colaborador_oportunidad', 'colaborador_oportunidad.id_oportunidad', 'status_oportunidad.id_oportunidad')
                    ->wherenull('colaborador_oportunidad.deleted_at')
                    ->wherenull('cat_status_oportunidad.deleted_at')
                    ->wherenull('status_oportunidad.deleted_at')
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.status as nombre','cat_status_oportunidad.color')
                    ->groupBy('cat_status_oportunidad.status')
                    ->get();

        return response()->json([
                    'message'=>'Correcto',
                    'error'=>false,
                    'data'=>[
                        'status'=>$catalogo_status,
                        'status_1'=>$status_1,
                        'status_2'=>$status_2,
                        'status_3'=>$status_3,
                        'select' => $catalogo_status_select
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
        $validador = $this->validateUpdateEtiqueta($request->all());

        $id = $request->id_etiqueta;

        if($validador->passes()){
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
        $errores = $validador->errors()->toArray();
        return response()->json([
            'error'=>true,
            'message'=>$errores
        ],400);

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
        $validador = $this->validadorServicio($request->all());

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
        $validador = $this->validadorServicio($request->all());
        
        $id = $request->id_servicio;

        if($validador->passes()){
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
        $errores = $validador->errors()->toArray();
        return response()->json([
            'error'=>true,
            'message'=>$errores
        ],400);
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

    public function updateColaborador(Request $request){
        $prospecto = Prospecto::where('id_prospecto',$request->id_prospecto)->first();    
        $colaborador = User::where('id',$request->id_colaborador)->first();

        $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto',$request->id_prospecto)->first();
        try{
            DB::beginTransaction();
           if($colaborador_prospecto){
                $colaborador_prospecto->id_colaborador = $colaborador->id;
                $colaborador_prospecto->save();
            }else{
                $colaborador_prospecto = new ColaboradorProspecto;
                $colaborador_prospecto->id_colaborador = $colaborador->id;
                $colaborador_prospecto->id_prospecto = $prospecto->id_prospecto;
                $colaborador_prospecto->save();
            }
            DB::commit();
            $data_event['colaborador'] = $colaborador;
            $data_event['prospecto'] = $prospecto;
            event(new AssignProspecto($data_event));
            return response()->json([
                'error'=>false,
                'message'=>'El prospecto se ha asignado correctamente.'
            ],201);
        }catch (Exception $e){
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
            'nombre' => 'required|string|max:50|unique:etiquetas,nombre,NULL,id_etiqueta,deleted_at,NULL',
            'descripcion'=>'string|max:150',

        ]);
    }

    public function validateUpdateEtiqueta(array $data){
        return Validator::make($data,[
            'nombre' => 'required|string|max:50|unique:etiquetas,nombre,'.$data['id_etiqueta'].',id_etiqueta,deleted_at,NULL',
            'descripcion'=>'string|max:150',

        ]);
    }

    public function validadorServicio(array $data){
        return Validator::make($data,[
            'nombre' => 'required|string|max:50|unique:cat_servicios,nombre,NULL,id_servicio_cat,deleted_at,NULL',
            'descripcion'=>'string|max:150',
        ]);
    }

    public function validateUpdateServicio(array $data){
        return Validator::make($data,[
            'nombre' => 'required|string|max:50|unique:cat_servicios,nombre,'.$data['id_servicio_cat'].',id_servicio_cat,deleted_at,NULL',
            'descripcion'=>'string|max:150',
        ]);
    }

    public function validatorMail(array $data){
      return Validator::make($data,[
          'email_de'=>'required|email',
          'nombre_de'=>'string|max:255',
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
                      ->wherenull('medio_contacto_prospectos.deleted_at')
                      //->groupBy('medio_contacto_prospectos.id_mediocontacto_catalogo')
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
                                                  ->wherenull('medio_contacto_oportunidades.deleted_at')
                                                  //->groupBy('medio_contacto_oportunidades.id_mediocontacto_catalogo')
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
    public function uploadFilesS3($file){
        //Sube archivos a bucket de Amazon
        $disk = Storage::disk('s3');
        $path = $file->store('temporales','s3');
        Storage::setVisibility($path,'public');
        return $disk->url($path);
    }

    public function after ($palabra, $inthat)
    {
        if (!is_bool(strpos($inthat, $palabra)))
        return substr($inthat, strpos($inthat,$palabra)+strlen($palabra));
    }

    public function sendMail (Request $request){
      //return $request->all();
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

            $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto', $request->id_prospecto)->first();
            if($colaborador_prospecto)
            {
                if($colaborador_prospecto->id_colaborador != $auth->id)
                {
                    $colaborador_prospecto->id_colaborador = $auth->id;
                    $colaborador_prospecto->save();
                }
            }
            else
            {
                $colaborador_prospecto = new ColaboradorProspecto;
                $colaborador_prospecto->id_colaborador = $auth->id;
                $colaborador_prospecto->id_prospecto = $request->id_prospecto;
                $colaborador_prospecto->save();
            }
            DB::commit();
        }
        if($request->id_colaborador){
            $colaborador = User::where('id',$request->id_colaborador)->first();
        }
        if(isset($request->id_prospecto_lista)){
            $prospecto_lista = [];
            foreach($request->id_prospecto_lista as $id_p){
                DB::beginTransaction();
                $prospecto = Prospecto::where('id_prospecto',$id_p)->first();
                
                $medio_contacto = new MedioContactoProspecto;
                $medio_contacto->id_mediocontacto_catalogo = 4;
                $medio_contacto->id_prospecto = $id_p;
                $medio_contacto->descripcion = 'Se envió correo desde Kiper.';
                $medio_contacto->fecha = Carbon::now();
                $medio_contacto->hora = Carbon::parse(Carbon::now())->format('H:i');
                
                $statusProspecto = StatusProspecto::where('id_prospecto',$id_p)->first();
                $statusProspecto->id_cat_status_prospecto = 1;
                $statusProspecto->save();
                $medio_contacto->save();

                $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto', $id_p)->first();
                if($colaborador_prospecto)
                {
                    if($colaborador_prospecto->id_colaborador != $auth->id)
                    {
                        $colaborador_prospecto->id_colaborador = $auth->id;
                        $colaborador_prospecto->save();
                    }
                }
                else
                {
                    $colaborador_prospecto = new ColaboradorProspecto;
                    $colaborador_prospecto->id_colaborador = $auth->id;
                    $colaborador_prospecto->id_prospecto = $id_p;
                    $colaborador_prospecto->save();
                }
                DB::commit();
                array_push($prospecto_lista, $prospecto);
            }
        }



        if(isset($request->Files))
        {
            $aux = '';
            foreach($data['email_para'] as $email_para) {
                $aux = $aux . $email_para . ',';
            }
            $aux = substr($aux, 0, -1);
            Mailgun::send('mailing.mail', $data, function ($message) use ($data,$request, $aux){
                $message->from($data['email_de'],$data['nombre_de']);
                $message->subject($data['asunto']);
                $message->bcc($data['email_de']);
                $message->to($aux);
                
                for($x = 0; $x < count($request->Files); $x++)
                {
                    $message->attach($request->Files[$x]->getRealPath(), $request->Files[$x]->getClientOriginalName());
                }   
            });
            
        }
        else
        {
            $aux = '';
            foreach($data['email_para'] as $email_para) {
                $aux = $aux . $email_para . ',';
            }            
            $aux = substr($aux, 0, -1);
            Mailgun::send('mailing.mail', $data, function ($message) use ($data, $aux){
                // $message->tag('myTag');
                $message->from($data['email_de'],$data['nombre_de']);
                // $message->testmode(true);
                $message->bcc($data['email_de']);
                $message->subject($data['asunto']);
                $message->to($aux);
            });
        }
       

       //Historial
        if($request->id_prospecto){
            $actividad = activity()
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Envió','color'=>'#7ac5ff'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un correo a :subject.nombre :subject.apellido </span>');

            event( new Historial($actividad));
            
        }elseif($request->id_colaborador){
            $actividad = activity()
                ->performedOn($colaborador)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Envió','color'=>'#7ac5ff'])
                ->useLog('prospecto')
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un correo a :subject.nombre :subject.apellido </span>');
            
            event( new Historial($actividad));
            
        }elseif($request->id_prospecto_lista){
            foreach($prospecto_lista as $prospecto){
                $actividad = activity()
                    ->performedOn($prospecto)
                    ->causedBy($auth)
                    ->withProperties(['accion'=>'Envió','color'=>'#7ac5ff'])
                    ->useLog('prospecto')
                    ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> :properties.accion un correo a :subject.nombre :subject.apellido </span>');

                event( new Historial($actividad));
            }
        }
                
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

        // $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto', $id)->first();
        // if($colaborador_prospecto)
        // {
        //     if($colaborador_prospecto->id_colaborador != $auth->id)
        //     {
        //         $colaborador_prospecto->id_colaborador = $auth->id;
        //         $colaborador_prospecto->save();
        //     }
        // }
        // else
        // {
        //     $colaborador_prospecto = new ColaboradorProspecto;
        //     $colaborador_prospecto->id_colaborador = $auth->id;
        //     $colaborador_prospecto->id_prospecto = $id;
        //     $colaborador_prospecto->save();
        // }
        DB::commit();

        $actividad = activity('prospecto')
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>'Cambió','color'=>'#7ac5ff'])
                ->log(':causer.nombre :causer.apellido <br> <span class="histroial_status"> Cambió de status a :subject.nombre :subject.apellido </span>');
        event( new Historial($actividad));
             

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

    public function GetIndustrias(){
        $indutrias = DB::table('cat_industrias')->wherenull('deleted_at')->get();
        return response()->json([
            'error'=>false,
            'data'=>$indutrias
        ],200);
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

          $colaborador_prospecto = ColaboradorProspecto::where('id_prospecto', $request->id_prospecto)->first();
            if($colaborador_prospecto)
            {
                if($colaborador_prospecto->id_colaborador != $auth->id)
                {
                    $colaborador_prospecto->id_colaborador = $auth->id;
                    $colaborador_prospecto->save();
                }
            }
            else
            {
                $colaborador_prospecto = new ColaboradorProspecto;
                $colaborador_prospecto->id_colaborador = $auth->id;
                $colaborador_prospecto->id_prospecto = $request->id_prospecto;
                $colaborador_prospecto->save();
            }


          DB::commit();
          
         
          
          $details_medio = MedioContactoProspecto::with('medio_contacto')->where('id_medio_contacto_prospecto',$medio_contacto_prospecto->id_medio_contacto_prospecto)->first();
          //Historial
          
                
            $actividad = activity('prospecto')
                ->performedOn($prospecto)
                ->causedBy($auth)
                ->withProperties(['accion'=>$details_medio->medio_contacto->nombre,'color'=>$details_medio->medio_contacto->color])
                ->log(':causer.nombre :causer.apellido<br> <span class="histroial_status"> Contactó vía :properties.accion a :subject.nombre :subject.apellido </span>');
          
                event( new Historial($actividad));
          
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
    public function grafica_por_status_por_fecha($status,$id,$inicio, $fin){
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
                ->whereBetween('status_oportunidad.updated_at', array($inicioPeriodo ,$finPeriodo))                
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
        return  DB::table('users')
            ->join('detalle_colaborador', 'users.id', 'detalle_colaborador.id_colaborador')
            ->join('colaborador_oportunidad','users.id','colaborador_oportunidad.id_colaborador')
            ->join('fotos_colaboradores', 'users.id', 'fotos_colaboradores.id_colaborador')
            ->join('status_oportunidad','colaborador_oportunidad.id_oportunidad', 'status_oportunidad.id_oportunidad')
            ->join('oportunidades', 'colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
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

    public function oportunidades_por_periodo_por_status($inicio, $fin, $status, $auth)
    {
        if($auth->role_id >= 2){
            return DB::table('oportunidades')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
            ->whereNull('oportunidades.deleted_at')
            ->wherenull('status_oportunidad.deleted_at')
            ->select('oportunidades.*')->where('status_oportunidad.id_cat_status_oportunidad','=',$status)->count();
        }
            return DB::table('oportunidades')
            ->join('status_oportunidad','oportunidades.id_oportunidad','status_oportunidad.id_oportunidad')
            ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
            ->where('colaborador_oportunidad.id_colaborador',$auth->id)
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

    public function ingresos_por_periodo_por_status($inicio, $fin, $status,$auth)
    {   
        if($auth->role_id >= 2){
            $ingresos = DB::table('oportunidades')
                ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                ->wherenull('oportunidades.deleted_at')
                ->wherenull('detalle_oportunidad.deleted_at')
                ->wherenull('status_oportunidad.deleted_at')
                ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                ->where('status_oportunidad.id_cat_status_oportunidad',$status)
                ->select(DB::raw('sum(detalle_oportunidad.valor * detalle_oportunidad.meses) as valor'))
                ->get();
            
            foreach ($ingresos as $ingreso) {
                $ingresos = $ingreso->valor;
            }
            return $ingresos;
        }
            $ingresos = DB::table('oportunidades')
                ->join('detalle_oportunidad','oportunidades.id_oportunidad','detalle_oportunidad.id_oportunidad')
                ->join('status_oportunidad','status_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                ->where('colaborador_oportunidad.id_colaborador',$auth->id)
                ->wherenull('oportunidades.deleted_at')
                ->wherenull('detalle_oportunidad.deleted_at')
                ->wherenull('status_oportunidad.deleted_at')
                ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                ->select(DB::raw('sum(detalle_oportunidad.valor * detalle_oportunidad.meses) as valor'))
                ->get();
            
            foreach ($ingresos as $ingreso) {
                $ingresos = $ingreso->valor;
            }
            return $ingresos;
            
    }

    public function origen_por_periodo($inicio, $fin,$auth)
    {
        if($auth->role_id >= 2){
            return DB::table('prospectos')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->wherenull('prospectos.deleted_at')
                ->wherenull('cat_fuentes.deleted_at')
                ->where('prospectos.deleted_at',null)
                ->select('cat_fuentes.nombre','cat_fuentes.url',DB::raw('count(*) as total, prospectos.fuente'))
                ->whereBetween('prospectos.updated_at', array($inicio ,$fin))
                ->groupBy('cat_fuentes.nombre')->get();   
        }
            return DB::table('prospectos')
                ->join('cat_fuentes','cat_fuentes.id_fuente','prospectos.fuente')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->where('colaborador_prospecto.id_colaborador',$auth->id)
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

    public function oportunidades_status_genericos(){
        return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->whereNull('colaborador_oportunidad.deleted_at')
                    ->whereNull('status_oportunidad.deleted_at')
                    ->whereNull('cat_status_oportunidad.deleted_at')
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get();     
    }

    public function oportunidades_status_genericos_por_fecha($inicio, $fin){
        return DB::table('oportunidades')
                    ->join('colaborador_oportunidad','colaborador_oportunidad.id_oportunidad','oportunidades.id_oportunidad')
                    ->join('status_oportunidad','status_oportunidad.id_oportunidad','colaborador_oportunidad.id_oportunidad')
                    ->join('cat_status_oportunidad','cat_status_oportunidad.id_cat_status_oportunidad','status_oportunidad.id_cat_status_oportunidad')
                    ->whereNull('oportunidades.deleted_at')
                    ->whereNull('colaborador_oportunidad.deleted_at')
                    ->whereNull('status_oportunidad.deleted_at')
                    ->whereNull('cat_status_oportunidad.deleted_at')
                    ->whereBetween('status_oportunidad.updated_at', array($inicio ,$fin))
                    ->select('cat_status_oportunidad.id_cat_status_oportunidad as id','cat_status_oportunidad.color',DB::raw('count(*) as total, cat_status_oportunidad.status'))->groupBy('cat_status_oportunidad.status')
                    ->get();     
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
    public function prospectos_sin_contactar($auth){
        if($auth->role_id >= 2){
            return DB::table('prospectos')
            ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
            ->wherenull('prospectos.deleted_at')
            ->wherenull('status_prospecto.deleted_at')
            ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
        }
            return DB::table('prospectos')
                ->join('status_prospecto','prospectos.id_prospecto','status_prospecto.id_prospecto')
                ->join('colaborador_prospecto','colaborador_prospecto.id_prospecto','prospectos.id_prospecto')
                ->where('colaborador_prospecto.id_colaborador',$auth->id)
                ->wherenull('prospectos.deleted_at')
                ->wherenull('status_prospecto.deleted_at')
                ->where('status_prospecto.id_cat_status_prospecto','=',2)->count();
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

    public function prospectosColaborador(){
        
       
        $prospecto_por_status = array();
        
        $estados = CatStatusProspecto::all();

        $colaboradores = ColaboradorProspecto::join('users', 'colaborador_prospecto.id_colaborador', 'users.id')
            ->wherenull('colaborador_prospecto.deleted_at')
            ->join('status_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
            ->whereNull('status_prospecto.deleted_at')
            ->join('prospectos', 'prospectos.id_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
            ->whereNull('prospectos.deleted_at')
            ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', 'status_prospecto.id_cat_status_prospecto')
            ->wherenull('cat_status_prospecto.deleted_at')
            ->join('detalle_colaborador', 'detalle_colaborador.id_colaborador', 'users.id')
            ->wherenull('detalle_colaborador.deleted_at')
            ->join('fotos_colaboradores', 'fotos_colaboradores.id_colaborador', 'users.id')
            ->whereNull('fotos_colaboradores.deleted_at')
            ->select('users.id as id_usuario', 'users.nombre as nombre', 'users.apellido as apellido', 'detalle_colaborador.puesto as puesto', 'fotos_colaboradores.url_foto as foto')
            ->groupBy('users.id')
            ->get();            

        foreach($colaboradores as $colaborador)
        {
            $status = array();
            foreach($estados as $estado)
            {
                $consulta = ColaboradorProspecto::join('users', 'colaborador_prospecto.id_colaborador', 'users.id')
                    ->wherenull('colaborador_prospecto.deleted_at')
                    ->join('status_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('status_prospecto.deleted_at')
                    ->join('prospectos', 'prospectos.id_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('prospectos.deleted_at')
                    ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', 'status_prospecto.id_cat_status_prospecto')
                    ->wherenull('cat_status_prospecto.deleted_at')
                    ->join('detalle_colaborador', 'detalle_colaborador.id_colaborador', 'users.id')
                    ->wherenull('detalle_colaborador.deleted_at')
                    ->join('fotos_colaboradores', 'fotos_colaboradores.id_colaborador', 'users.id')
                    ->whereNull('fotos_colaboradores.deleted_at')
                    ->where('users.id', '=', $colaborador->id_usuario)
                    ->where('cat_status_prospecto.id_cat_status_prospecto','=',$estado->id_cat_status_prospecto)
                    ->select(DB::raw('concat(users.nombre, " ", users.apellido) as colaborador_nombre' ), DB::raw('count(status_prospecto.id_status_prospecto) as total'), 'cat_status_prospecto.status as status', 'cat_status_prospecto.color as color')
                    ->get();

                array_push($status,  $consulta);
            }
            array_push($prospecto_por_status,  $status);
        }
        

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'prospecto_por_status'=>$prospecto_por_status,
                'estados'=>$estados,
                'colaboradores'=>$colaboradores
            ]
            ],200);
    }

    public function prospectosColaborador_por_fecha($inicio, $fin){

        $inicioPeriodo = (new Carbon($inicio))->addDays(-1);
        $finPeriodo = (new Carbon(($fin)))->addDays(1);
       
        $prospecto_por_status = array();
        $total = array();
        $estados = CatStatusProspecto::all();

        $colaboradores = $this->ColaboradProspectoQuery($inicioPeriodo,$finPeriodo);

        foreach($colaboradores as $colaborador)
        {
            $status = array();
            
            foreach($estados as $estado)
            {
                $consulta = $this->ColaboradorProspectoStatusQuery($colaborador->id_usuario,$estado->id_cat_status_prospecto,$inicioPeriodo,$finPeriodo);
               
                    if($consulta[0]->colaborador_nombre != null){
                        array_push($status,  $consulta);
                    }else {
                        $consulta[0]->colaborador_nombre = $colaborador->nombre.' '.$colaborador->apellido;
                        $consulta[0]->total = 0;
                        $consulta[0]->status = $estado->status;
                        $consulta[0]->color = $estado->color;
                        array_push($status,  $consulta);

                    }

            }
           
            
            array_push($prospecto_por_status,  $status);
        }
        

        return response()->json([
            'message'=>'Success',
            'error'=>false,
            'data'=>[
                'prospecto_por_status'=>$prospecto_por_status,
                'estados'=>$estados,
                'colaboradores'=>$colaboradores,
                'totales'=>$total
            ]
            ],200);
    }

    //Auxiliares
    
    public function ColaboradorProspectoStatusQuery($id_user,$id_status,$inicio,$fin){
        return ColaboradorProspecto::join('users', 'colaborador_prospecto.id_colaborador', 'users.id')
                    ->wherenull('colaborador_prospecto.deleted_at')
                    ->join('status_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('status_prospecto.deleted_at')
                    ->join('prospectos', 'prospectos.id_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('prospectos.deleted_at')
                    ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', 'status_prospecto.id_cat_status_prospecto')
                    ->wherenull('cat_status_prospecto.deleted_at')
                    ->join('detalle_colaborador', 'detalle_colaborador.id_colaborador', 'users.id')
                    ->wherenull('detalle_colaborador.deleted_at')
                    ->join('fotos_colaboradores', 'fotos_colaboradores.id_colaborador', 'users.id')
                    ->whereNull('fotos_colaboradores.deleted_at')
                    ->where('users.id', '=', $id_user)
                    ->where('cat_status_prospecto.id_cat_status_prospecto','=',$id_status)
                    ->whereBetween('status_prospecto.updated_at', array($inicio ,$fin))
                    ->select(DB::raw('concat(users.nombre, " ", users.apellido) as colaborador_nombre' ), DB::raw('count(status_prospecto.id_status_prospecto) as total'),'cat_status_prospecto.status as status', 'cat_status_prospecto.color as color')
                    ->get();
    }
    public function ColaboradProspectoQuery($inicio, $fin){
        return ColaboradorProspecto::join('users', 'colaborador_prospecto.id_colaborador', 'users.id')
                    ->wherenull('colaborador_prospecto.deleted_at')
                    ->join('status_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('status_prospecto.deleted_at')
                    ->join('prospectos', 'prospectos.id_prospecto', 'status_prospecto.id_prospecto', 'colaborador_prospecto.id_prospecto')
                    ->whereNull('prospectos.deleted_at')
                    ->join('cat_status_prospecto', 'cat_status_prospecto.id_cat_status_prospecto', 'status_prospecto.id_cat_status_prospecto')
                    ->wherenull('cat_status_prospecto.deleted_at')
                    ->join('detalle_colaborador', 'detalle_colaborador.id_colaborador', 'users.id')
                    ->wherenull('detalle_colaborador.deleted_at')
                    ->join('fotos_colaboradores', 'fotos_colaboradores.id_colaborador', 'users.id')
                    ->whereNull('fotos_colaboradores.deleted_at')
                    ->select('users.id as id_usuario', 'users.nombre as nombre', 'users.apellido as apellido', 'detalle_colaborador.puesto as puesto', 'fotos_colaboradores.url_foto as foto',DB::raw('count(colaborador_prospecto.id_colaborador) as total_leads'))
                    ->whereBetween('status_prospecto.updated_at', array($inicio ,$fin))
                    ->groupBy('users.id')
                    ->get();            
    }

}

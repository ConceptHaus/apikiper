<?php

namespace App\Http\Controllers\Recordatorios;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AuthService;
use Illuminate\Http\Request;
use App\Http\Services\Recordatorios\RecordatoriosService;

class RecordatoriosController extends Controller
{
    public function sendAlerts()
    {
        $this->getRecordatoriosOportunidades();
        $this->getRecordatoriosProspectos();
        $this->getRecordatoriosUsuarios();
    }

    public function getRecordatoriosOportunidades(){
        return RecordatoriosService::getRecordatoriosOportunidades();
    }

    public function getRecordatoriosProspectos(){
        return RecordatoriosService::getRecordatoriosProspectos();
    }

    public function getRecordatoriosUsuarios(){
        return RecordatoriosService::getRecordatoriosUsuarios();
    }

}

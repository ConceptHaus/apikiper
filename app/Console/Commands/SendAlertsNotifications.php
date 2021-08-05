<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Recordatorios\RecordatoriosController;
use DB;

class SendAlertsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts_notifications:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de alertas de oportunidad, prospecto y usuario';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $alerts = new RecordatoriosController;
        $alerts->sendAlerts();

        $this->info('Se ejecuto la función de enviar alertas con exito');
    }
}
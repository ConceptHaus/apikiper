<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        'App\Events\Historial' => [
            'App\Listeners\HistorialListener'
        ],
        'App\Events\NewLead' => [
            'App\Listeners\NewLeadListener'
        ],
        'App\Events\NewCall' => [
            'App\Listeners\NewCallListener'
        ],
        'App\Events\CoCan'=>[
            'App\Listeners\ColaboraCanListener'
        ],
        'App\Events\CoGdl'=>[
            'App\Listeners\ColaboraGdlListener'
        ],
        'App\Events\NewAssigment'=>[
            'App\Listeners\NewAssigmentListener'
        ],
        'App\Events\AssignProspecto'=>[
            'App\Listeners\AssignProspectoListener'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

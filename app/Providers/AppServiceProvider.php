<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
            $this->app->bind('mailgun.client', function() {
                return \Http\Adapter\Guzzle6\Client::createWithConfig([
                    // your Guzzle6 configuration
                ]);
            });
    }
}

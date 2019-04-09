<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setLocale(LC_TIME, 'sv');
        Carbon::setLocale('sv');
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
            
            $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
            $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);

    }
}

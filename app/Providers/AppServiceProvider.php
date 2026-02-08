<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        if (Auth::check()) {
            $config = ConfiguracionMAc::where('IDCENTRO_MAC', Auth::user()->idcentro_mac)->first();
    
            if ($config) {
                config(['database.connections.mysql2.url' => $config->url]);
            }
        }
    }
}

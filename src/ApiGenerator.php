<?php

namespace Zakalajo\ApiGenerator;

use Illuminate\Support\ServiceProvider;
use Zakalajo\ApiGenerator\Commands\Scaffold;
use Zakalajo\ApiGenerator\Commands\UserCreate;

class ApiGenerator extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        $this->loadViewsFrom(__DIR__ . '/templates', 'apigenerator');

        $this->commands([
            Scaffold::class,
            UserCreate::class,
        ]);
        //
    }
}

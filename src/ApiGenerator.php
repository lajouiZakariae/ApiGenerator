<?php

namespace Zakalajo\ApiGenerator;

use Illuminate\Support\ServiceProvider;
use Zakalajo\ApiGenerator\Commands\ScaffoldAll;
use Zakalajo\ApiGenerator\Commands\ScaffoldTable;
use Zakalajo\ApiGenerator\Commands\User;

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
            ScaffoldAll::class,
            User::class,
        ]);
        //
    }
}

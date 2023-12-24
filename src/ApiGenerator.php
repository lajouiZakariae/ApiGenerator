<?php

namespace Zakalajo\ApiGenerator;

use Illuminate\Support\ServiceProvider;
use Zakalajo\ApiGenerator\Commands\ApiCreate;
use Zakalajo\ApiGenerator\Commands\FactoryCreate;
use Zakalajo\ApiGenerator\Commands\FormRequestCreate;
use Zakalajo\ApiGenerator\Commands\ModelCreate;
use Zakalajo\ApiGenerator\Commands\ResourceCreate;
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
            ApiCreate::class,
            UserCreate::class,
            ResourceCreate::class,
            ModelCreate::class,
            FormRequestCreate::class,
            FactoryCreate::class,
        ]);
    }
}

<?php

namespace Zakalajo\ApiGenerator;

use Illuminate\Support\ServiceProvider;
use Zakalajo\ApiGenerator\Commands\ApiCreate;
use Zakalajo\ApiGenerator\Commands\FactoryCreate;
use Zakalajo\ApiGenerator\Commands\FormRequestCreate;
use Zakalajo\ApiGenerator\Commands\ModelCreate;
use Zakalajo\ApiGenerator\Commands\ResourceCreate;
use Zakalajo\ApiGenerator\Commands\UserCreate;
use Illuminate\Support\Str;
use Zakalajo\ApiGenerator\Commands\ControllerCreate;

class ApiGeneratorServiceProvider extends ServiceProvider {
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
        Str::macro('modelName', function ($string) {
            return str($string)->singular()->camel()->ucfirst();
        });

        $this->loadViewsFrom(__DIR__ . '/templates', 'apigenerator');

        $this->commands([
            ApiCreate::class,
            UserCreate::class,
            ResourceCreate::class,
            ModelCreate::class,
            FormRequestCreate::class,
            ControllerCreate::class,
            FactoryCreate::class,
        ]);
    }
}

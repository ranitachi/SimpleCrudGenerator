<?php

namespace Fcn\SimpleCrudGenerator;

use Illuminate\Support\ServiceProvider;

class SimpleCrudGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            \Fcn\SimpleCrudGenerator\Commands\GenerateCrudCommand::class,
        ]);
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views/stubs/blade', 'simple-crud');

        $this->publishes([
            __DIR__.'/resources/views/stubs/blade' => resource_path('views/vendor/simple-crud'),
        ], 'simple-crud-views');

        $this->publishes([
            __DIR__.'/Stubs' => base_path('stubs/simple-crud'),
        ], 'simple-crud-stubs');

    }
}
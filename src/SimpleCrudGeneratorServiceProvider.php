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
        // Load default blade view stub path (for render fallback)
        $this->loadViewsFrom(__DIR__.'/../resources/views/stubs/blade', 'simple-crud');

        // Publish views to vendor for customization
        $this->publishes([
            __DIR__.'/../resources/views/stubs/blade' => resource_path('views/vendor/simple-crud'),
        ], 'simple-crud-views');

        // Optional: publish stubs for customization
        $this->publishes([
            __DIR__.'/../Stubs' => base_path('stubs/simple-crud'),
        ], 'simple-crud-stubs');
    }
}
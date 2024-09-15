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
        // Load routes, views, or migrations if needed
    }
}


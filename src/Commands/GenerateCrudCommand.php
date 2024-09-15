<?php

namespace Fcn\SimpleCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateCrudCommand extends Command
{
    protected $signature = 'make:simple-crud {table}';
    protected $description = 'Generate CRUD files based on table name';

    public function handle()
    {
        $table = $this->argument('table');
        $model = Str::studly(Str::singular($table));

        // Check directories and create if they don't exist
        $this->checkAndCreateDirectory(app_path('Models'));
        $this->checkAndCreateDirectory(app_path('Http/Controllers'));
        $this->checkAndCreateDirectory(app_path('Http/Requests'));
        $this->checkAndCreateDirectory(app_path('Services'));
        $this->checkAndCreateDirectory(database_path('migrations'));

        // Generate files
        $this->generateMigration($table);
        $this->generateModel($table, $model);
        $this->generateRequest($model);
        $this->generateService($model);
        $this->generateController($model);
        $this->info("CRUD for {$model} generated successfully.");
    }

    protected function checkAndCreateDirectory($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("Directory created: {$path}");
        }
    }

    protected function getStub($type)
    {
        return file_get_contents(__DIR__ . "/../Stubs/{$type}.stub");
    }

    protected function generateMigration($table)
    {
        $table = strtolower($table);
        $stub = $this->getStub('migration');
        $migrationContent = str_replace('{{table}}', strtolower($table), $stub);
        $migrationFile = database_path('migrations') . '/' . date('Y_m_d_His') . "_create_{$table}_table.php";
        File::put($migrationFile, $migrationContent);
        $this->info("Migration created: {$migrationFile}");
    }

    protected function generateModel($table, $model)
    {
        $stub = $this->getStub('model');
        $modelContent = str_replace(['{{table}}', '{{model}}'], [strtolower($table), $model], $stub);
        $modelFile = app_path("Models/{$model}.php");
        File::put($modelFile, $modelContent);
        $this->info("Model created: {$modelFile}");
    }

    protected function generateRequest($model)
    {
        $stub = $this->getStub('request');
        $requestContent = str_replace('{{model}}', $model, $stub);
        $requestFile = app_path("Http/Requests/{$model}Request.php");
        File::put($requestFile, $requestContent);
        $this->info("Request created: {$requestFile}");
    }

    protected function generateService($model)
    {
        $stub = $this->getStub('service');
        $serviceContent = str_replace('{{model}}', $model, $stub);
        $serviceFile = app_path("Services/{$model}Service.php");
        File::put($serviceFile, $serviceContent);
        $this->info("Service created: {$serviceFile}");
    }

    protected function generateController($model)
    {
        $stub = $this->getStub('controller');
        $controllerContent = str_replace('{{model}}', $model, $stub);
        $controllerFile = app_path("Http/Controllers/{$model}Controller.php");
        File::put($controllerFile, $controllerContent);
        $this->info("Controller created: {$controllerFile}");
    }

}

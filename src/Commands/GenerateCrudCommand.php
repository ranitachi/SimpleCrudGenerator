<?php

namespace Fcn\SimpleCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Fcn\SimpleCrudGenerator\CrudSchemaParser;

class GenerateCrudCommand extends Command
{
    protected $signature = 'make:simple-crud {table}';
    protected $description = 'Generate CRUD files based on table name';

    public function handle()
    {
        $table = $this->argument('table');
        $model = Str::studly(Str::singular($table));

        $columns = CrudSchemaParser::getColumns($table);
        $fieldArray = array_map(fn($col) => CrudSchemaParser::generateField($col), $columns);
        $rulesArray = array_column($columns, 'rule', 'name');
        $fillableArray = array_column($columns, 'name');
        $datatableArray = array_map(fn($col) => [
            'label' => Str::headline($col['name']),
            'data' => $col['name'],
        ], $columns);

        $fillable = CrudSchemaParser::formatArray($fillableArray);
        $rules = CrudSchemaParser::formatArray($rulesArray);
        $fields = CrudSchemaParser::formatArray($fieldArray);
        $datatable = CrudSchemaParser::formatArray($datatableArray);

        $this->checkAndCreateDirectory(app_path('Models'));
        $this->checkAndCreateDirectory(app_path('Http/Controllers'));
        $this->checkAndCreateDirectory(app_path('Http/Requests'));
        $this->checkAndCreateDirectory(app_path('Services'));
        $this->checkAndCreateDirectory(database_path('migrations'));

        $this->generateMigration($table);
        $this->generateModel($table, $model, $fillable);
        $this->generateRequest($model, $rules);
        $this->generateService($model, $fields, $datatable);
        $this->generateController($model);

        $this->info("✅ CRUD for {$model} generated successfully.");
    }

    protected function checkAndCreateDirectory($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Directory created: {$path}");
        }
    }

    protected function getStub($type)
    {
        return file_get_contents(__DIR__ . "/../Stubs/{$type}.stub");
    }

    protected function generateMigration($table)
    {
        $stub = $this->getStub('migration');
        $migrationContent = str_replace('{{table}}', strtolower($table), $stub);
        $migrationFile = database_path('migrations') . '/' . date('Y_m_d_His') . "_create_{$table}_table.php";
        File::put($migrationFile, $migrationContent);
        $this->info("📦 Migration created: {$migrationFile}");
    }

    protected function generateModel($table, $model, $fillable)
    {
        $stub = $this->getStub('model');
        $stub = str_replace(['{{table}}', '{{model}}', '{{fillable}}'], [strtolower($table), $model, $fillable], $stub);
        $modelFile = app_path("Models/{$model}.php");
        File::put($modelFile, $stub);
        $this->info("🧩 Model created: {$modelFile}");
    }

    protected function generateRequest($model, $rules)
    {
        $stub = $this->getStub('request');
        $stub = str_replace(['{{model}}', '{{rules}}'], [$model, $rules], $stub);
        $requestFile = app_path("Http/Requests/{$model}Request.php");
        File::put($requestFile, $requestFile);
        File::put($requestFile, $stub);
        $this->info("🛡️ Request created: {$requestFile}");
    }

    protected function generateService($model, $fields, $datatable)
    {
        $stub = $this->getStub('service');
        $stub = str_replace(['{{model}}', '{{fields}}', '{{datatable}}'], [$model, $fields, $datatable], $stub);
        $serviceFile = app_path("Services/{$model}Service.php");
        File::put($serviceFile, $stub);
        $this->info("⚙️ Service created: {$serviceFile}");
    }

    protected function generateController($model)
    {
        $stub = $this->getStub('controller');
        $stub = str_replace('{{model}}', $model, $stub);
        $controllerFile = app_path("Http/Controllers/{$model}Controller.php");
        File::put($controllerFile, $stub);
        $this->info("🎮 Controller created: {$controllerFile}");
    }
}

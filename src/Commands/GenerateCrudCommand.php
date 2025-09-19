<?php

namespace Fcn\SimpleCrudGenerator\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Fcn\SimpleCrudGenerator\Helpers\CrudSchemaParser;

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
        // JANGAN di-format dulu di sini!
        // $datatable = CrudSchemaParser::formatArray($datatableArray);

        $this->checkAndCreateDirectory(app_path('Models'));
        $this->checkAndCreateDirectory(app_path('Http/Controllers'));
        $this->checkAndCreateDirectory(app_path('Http/Requests'));
        $this->checkAndCreateDirectory(app_path('Services'));
        $this->checkAndCreateDirectory(database_path('migrations'));

        $this->generateMigration($table);
        $this->generateModel($table, $model, $fillable);
        $this->generateRequest($model, $rules);
        $this->generateService($model, $fields, $datatableArray); // Kirim array asli
        $this->generateController($model, Str::slug($table));
        $this->generateBladeViews($table, Str::headline($table));

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
        File::put($requestFile, $stub);
        $this->info("🛡️ Request created: {$requestFile}");
    }

    protected function generateService($model, $fields, $datatableArray)
    {
        $stub = $this->getStub('service');

        // Wrap datatable with default "No" and "Aksi" entries
        $datatableWrapped = array_merge(
            [['label' => 'No', 'data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false]],
            $datatableArray,
            [['label' => 'Aksi', 'data' => 'action', 'name' => 'action', 'orderable' => false, 'searchable' => false]]
        );

        // Format after wrapping
        $datatable = CrudSchemaParser::formatArray($datatableWrapped, 3);

        $stub = str_replace(['{{model}}', '{{fields}}', '{{datatable}}'], [$model, $fields, $datatable], $stub);

        $serviceFile = app_path("Services/{$model}Service.php");
        File::put($serviceFile, $stub);
        $this->info("⚙️ Service created: {$serviceFile}");
    }

    protected function generateController($model, $table)
    {
        $stub = $this->getStub('controller');
        $stub = str_replace('{{model}}', $model, $stub);
        $stub = str_replace('{{table}}', $table, $stub);
        $controllerFile = app_path("Http/Controllers/{$model}Controller.php");
        File::put($controllerFile, $stub);
        $this->info("🎮 Controller created: {$controllerFile}");
    }

    protected function generateBladeViews(string $table, string $title)
    {
        $viewPath = resource_path("views/vendor/simple-crud/{$table}");
        File::ensureDirectoryExists($viewPath);

        foreach (['index', 'create', 'edit'] as $view) {
            $stub = file_get_contents(__DIR__ . "/../resources/views/stubs/blade/{$view}.stub");
            $content = str_replace([
                '{{table}}',
                '{{title}}',
                '{{model}}'
            ], [
                $table,
                $title,
                Str::studly(Str::singular($table))
            ], $stub);

            File::put("{$viewPath}/{$view}.blade.php", $content);
            $this->info("📄 Created view: {$viewPath}/{$view}.blade.php");
        }
    }
}

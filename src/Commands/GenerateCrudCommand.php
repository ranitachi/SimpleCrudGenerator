<?php

namespace Fcn\SimpleCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Fcn\SimpleCrudGenerator\Helpers\CrudSchemaParser;

class GenerateCrudCommand extends Command
{
    protected $signature = 'make:simple-crud {table} {--force}';
    protected $description = 'Generate CRUD files based on table name with dynamic parsing';

    public function handle()
    {
        $table = $this->argument('table');
        $model = Str::studly(Str::singular($table));
        $title = Str::headline(Str::singular($table));

        if (!Schema::hasTable($table) && !$this->option('force')) {
            $this->error("Tabel '{$table}' belum ada di database.");
            $this->warn("Silakan buat tabel atau gunakan opsi --force jika ingin lanjut.");
            return;
        }

        $columns = Schema::hasTable($table)
            ? CrudSchemaParser::getColumns($table)
            : [];

        $fillable = implode(",\n", array_map(fn($col) => "        '" . $col['name'] . "'", $columns));
        $rules = implode(",\n", array_map(fn($col) => "            '" . $col['name'] . "' => '" . $col['rule'] . "'", $columns));
        $fields = implode(",\n", array_map(fn($col) => "            ['type' => '" . $col['input'] . "', 'name' => '" . $col['name'] . "', 'label' => '" . Str::headline($col['name']) . "']", $columns));
        $datatable = implode(",\n", array_map(fn($col) => "            ['label' => '" . Str::headline($col['name']) . "', 'data' => '" . $col['name'] . "']", $columns));

        $this->generateFile('model', 'Models', "{$model}.php", [
            '{{model}}' => $model,
            '{{table}}' => $table,
            '{{fillable}}' => $fillable,
        ]);

        $this->generateFile('request', 'Http/Requests', "{$model}Request.php", [
            '{{model}}' => $model,
            '{{rules}}' => $rules,
        ]);

        $this->generateFile('service', 'Services', "{$model}Service.php", [
            '{{model}}' => $model,
            '{{fields}}' => $fields,
            '{{datatable}}' => $datatable,
        ]);

        $this->generateFile('controller', 'Http/Controllers', "{$model}Controller.php", [
            '{{model}}' => $model,
        ]);

        $this->generateMigration($table);
        $this->generateBladeViews($table, $title);
        $this->generateRouteEntry($table, $model);

        $this->info("CRUD for {$model} generated successfully!");
    }

    protected function generateFile(string $stubName, string $subPath, string $fileName, array $replacements)
    {
        $stub = file_get_contents(__DIR__ . "/../Stubs/{$stubName}.stub");
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        $filePath = app_path("{$subPath}/{$fileName}");
        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $content);

        $this->info("Created: {$filePath}");
    }

    protected function generateMigration(string $table)
    {
        $stub = file_get_contents(__DIR__ . '/../Stubs/migration.stub');
        $content = str_replace('{{table}}', $table, $stub);

        $filePath = database_path('migrations/' . date('Y_m_d_His') . "_create_{$table}_table.php");
        File::put($filePath, $content);

        $this->info("Created: {$filePath}");
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
            $this->info("Created view: {$viewPath}/{$view}.blade.php");
        }
    }

    protected function generateRouteEntry(string $table, string $model)
    {
        $routePath = base_path('routes/web.php');
        $route = "\nRoute::resource('{$table}', App\\Http\\Controllers\\{$model}Controller::class);";

        if (!Str::contains(file_get_contents($routePath), $route)) {
            File::append($routePath, $route);
            $this->info("Added route to routes/web.php");
        } else {
            $this->warn("Route already exists in routes/web.php");
        }
    }
}

<?php

namespace Fcn\SimpleCrudGenerator\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrudSchemaParser
{
    public static function getColumns(string $table): array
    {
        $schema = DB::getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        $results = [];

        foreach ($columns as $column) {
            $type = $schema->getColumnType($table, $column);
            $results[$column] = [
                'name' => $column,
                'type' => $type,
                'required' => !Str::contains($column, ['_at', 'uuid', 'deleted']),
                'input' => self::mapInputType($column, $type),
                'rule'  => self::mapRule($column, $type),
            ];
        }

        return $results;
    }

    protected static function mapInputType(string $name, string $type): string
    {
        if (Str::contains($name, ['logo', 'photo', 'picture'])) return 'file';
        if (Str::contains($name, ['flag', 'status'])) return 'select';
        if (Str::contains($name, ['desc', 'content'])) return 'textarea';
        if ($type === 'integer') return 'number';
        if ($type === 'text') return 'textarea';
        return 'text';
    }

    protected static function mapRule(string $name, string $type): string
    {
        $rules = [];

        if (!Str::contains($name, ['_at', 'deleted'])) {
            $rules[] = 'nullable';
        }

        switch ($type) {
            case 'integer': $rules[] = 'integer'; break;
            case 'string': $rules[] = 'string'; break;
            case 'text': $rules[] = 'string'; break;
            case 'boolean': $rules[] = 'boolean'; break;
        }

        if (Str::contains($name, 'email')) $rules[] = 'email';
        if (Str::contains($name, 'uuid')) $rules[] = 'uuid';

        return implode('|', array_unique($rules));
    }
}

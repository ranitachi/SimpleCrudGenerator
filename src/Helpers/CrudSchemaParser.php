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

    public static function generateField($column): array
    {
        $name = $column['name'];
        $type = $column['type'];
        $label = Str::title(str_replace('_', ' ', $name));

        // Default col width
        $col = 12;

        // Deteksi kolom status/flag/flag_active
        if (in_array($name, ['flag', 'status', 'flag_active'])) {
            return [
                'type' => 'select',
                'name' => $name,
                'label' => $label,
                'options' => [
                    ['label' => 'Aktif', 'value' => 1],
                    ['label' => 'Nonaktif', 'value' => 0],
                ],
                'value' => "old('{$name}', \$data->{$name} ?? 1)",
                'col' => $col,
            ];
        }

        // Deteksi file/image
        if (Str::contains($name, ['image', 'photo', 'picture'])) {
            return [
                'type' => 'file',
                'name' => $name,
                'label' => $label,
                'preview' => true,
                'value' => "\$data ? asset('storage/' . \$data->{$name}) : 'https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'",
                'col' => $col,
            ];
        }

        // Deteksi textarea untuk kolom tertentu
        if (in_array($name, ['desc', 'deskripsi', 'content'])) {
            return [
                'type' => 'textarea',
                'name' => $name,
                'label' => $label,
                'value' => "old('{$name}', \$data->{$name} ?? '')",
                'wysiwyg' => true,
                'col' => $col,
            ];
        }

        // Default input type
        $inputType = match ($type) {
            'integer', 'bigint', 'smallint' => 'number',
            'date', 'datetime', 'timestamp' => 'date',
            default => 'text',
        };

        return [
            'type' => $inputType,
            'name' => $name,
            'label' => $label,
            'value' => "old('{$name}', \$data->{$name} ?? '')",
            'col' => $col,
        ];
    }

    public static function formatArray($array, int $indent = 2): string
    {
        return self::formatArrayRecursive($array, $indent);
    }

    protected static function formatArrayRecursive($array, int $indent = 2): string
    {
        $result = "[\n";
        $pad = str_repeat('    ', $indent);

        foreach ($array as $key => $value) {
            $keyPart = is_numeric($key) ? '' : "'$key' => ";

            if (is_array($value)) {
                $valStr = self::formatArrayRecursive($value, $indent + 1);
                $result .= "$pad$keyPart$valStr,\n";
            } else {
                $valStr = self::formatValue($value);
                $result .= "$pad$keyPart$valStr,\n";
            }
        }

        $result .= str_repeat('    ', $indent - 1) . "]";
        return $result;
    }

    protected static function formatValue($value): string
    {
        if (is_string($value)) {
            if (
                Str::startsWith($value, 'old(') ||
                Str::startsWith($value, '$data') ||
                Str::startsWith($value, 'asset(')
            ) {
                return $value;
            }
            return "'" . str_replace("'", "\\'", $value) . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        return (string) $value;
    }


    // public static function formatArray(array $array, int $indent = 3): string
    // {
    //     $result = "[\n";
    //     $indentSpace = str_repeat('    ', $indent);

    //     foreach ($array as $item) {
    //         $result .= $indentSpace . "[\n";

    //         foreach ($item as $key => $value) {
    //             $keyStr = is_numeric($key) ? $key : "'$key'";
    //             $valStr = is_array($value)
    //                 ? self::formatArrayInline($value, $indent + 1)
    //                 : self::formatValue($value);

    //             $result .= str_repeat('    ', $indent + 1) . "$keyStr => $valStr,\n";
    //         }

    //         $result .= $indentSpace . "],\n";
    //     }

    //     $result .= str_repeat('    ', $indent - 1) . "]";
    //     return $result;
    // }

    protected static function formatArrayInline(array $arr, int $indent = 4): string
    {
        $result = "[\n";
        foreach ($arr as $item) {
            $val = self::formatValue($item);
            $result .= str_repeat('    ', $indent) . "$val,\n";
        }
        $result .= str_repeat('    ', $indent - 1) . "]";
        return $result;
    }

    // protected static function formatValue($value): string
    // {
    //     if (is_string($value)) {
    //         if (Str::startsWith($value, 'old(') || Str::startsWith($value, '$data')) {
    //             return $value; // biarkan sebagai ekspresi PHP
    //         }
    //         return "'" . str_replace("'", "\\'", $value) . "'";
    //     }

    //     if (is_bool($value)) {
    //         return $value ? 'true' : 'false';
    //     }

    //     if (is_null($value)) {
    //         return 'null';
    //     }

    //     return (string) $value;
    // }

}

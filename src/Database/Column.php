<?php

namespace Zakalajo\ApiGenerator\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Column
{
    private string $table;

    private string $name;

    private string $type;

    private bool $nullable;

    private mixed $default_value;

    private bool $increments;

    private bool $unsigned;

    private ?int $char_max_length;

    private ?int $max_length;

    private ?int $numeric_scale;

    private ?int $numeric_precision;

    private ?Collection $allowed_values;

    private bool $is_primary;

    private bool $is_foreign;

    private  ?object $foreign = null;

    private  ?Collection $has_many = null;

    public function __construct(
        string $table,
        string $column_name,
        string $data_type,
        ?string $column_default,
        ?string $char_max_length,
        $numeric_scale,
        $numeric_precision,
        string $column_type,
        string $extra,
        string $is_nullable,
        string $column_key
    ) {
        $this->table = $table;

        $this->name = $column_name;

        $this->type = $data_type;

        $this->default_value = $column_default;

        $this->char_max_length = $char_max_length;

        $this->max_length = intval(substr($column_type, strpos($column_type, '(') + 1, -1));

        $this->nullable = $is_nullable === 'YES';

        /* Numeric Values */
        $this->increments = $extra === 'auto_increment';

        $this->numeric_scale = $numeric_scale;

        $this->numeric_precision = $numeric_precision;

        $this->allowed_values = in_array($this->type, ['enum', 'set']) ? $this->extractValuesFromType($column_type) : null;

        $this->unsigned = str_contains($column_type, 'unsigned');

        /* Keys */
        $this->is_primary = $column_key === 'PRI';

        $this->is_foreign = $column_key === 'MUL';

        if ($this->is_foreign) $this->loadForeignKey();

        if ($this->is_primary) $this->loadHasManyRelations();
    }

    function isPrimary(): bool
    {
        return $this->is_primary;
    }

    function isForeign(): bool
    {
        return $this->is_foreign;
    }

    function getHasMany(): ?Collection
    {
        return $this->has_many;
    }

    function getName(): string
    {
        return $this->name;
    }

    function getType(): string
    {
        return $this->type;
    }

    function isNullable(): bool
    {
        return $this->nullable;
    }

    function getMaxLength(): int
    {
        return $this->max_length;
    }

    function getCharMexLength(): int
    {
        return $this->char_max_length;
    }

    function getForeign(): ?object
    {
        return $this->foreign;
    }

    function getAllowedValues(): ?Collection
    {
        return $this->allowed_values;
    }

    function isEnum(): bool
    {
        return $this->type === 'enum';
    }

    function isUnSigned(): bool
    {
        return $this->unsigned;
    }

    function isTextual(): bool
    {
        return in_array($this->type, ['varchar', 'char', 'smalltext', 'longtext', 'tinytext', 'text']);
    }

    function isFloat(): bool
    {
        return in_array($this->type, ['float', 'double', 'decimal']);
    }

    function isInteger(): bool
    {
        return $this->type === 'int';
    }

    function isBigInt(): bool
    {
        return $this->type === 'bigint';
    }

    private function loadForeignKey(): void
    {
        $result = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', $this->table)
            ->where('COLUMN_NAME', $this->name)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->first(['REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME']);

        if ($result) {
            $this->foreign = (object) [
                'parent_table' => $result->REFERENCED_TABLE_NAME,
                'parent_column' => $result->REFERENCED_COLUMN_NAME,
                'child_table' => $this->table,
                'child_column' => $this->name,
            ];
        }
    }

    /**
     * Loads has many relationships
     */
    private function loadHasManyRelations(): void
    {
        $result = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('REFERENCED_TABLE_NAME', $this->table)
            ->where('REFERENCED_COLUMN_NAME', $this->name)
            ->get();

        if ($result->isNotEmpty()) {
            $this->has_many = collect();

            $result->each(function ($reference) {

                $this->has_many->add((object)[
                    'parent_table' => $this->table,
                    'parent_column' => $this->name,
                    'child_table' => $reference->TABLE_NAME,
                    'child_column' => $reference->COLUMN_NAME,
                ]);
            });
        }
    }

    /**
     * Extract allowed values for sets and enums
     */
    private function extractValuesFromType(string $type): Collection
    {
        return str($type)
            ->substr(strpos($type, '(') + 2, -2)
            ->explode("','");
    }
}

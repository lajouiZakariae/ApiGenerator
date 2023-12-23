<?php

namespace Zakalajo\ApiGenerator\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Column {
    private string $table;

    private string $name;

    private string $type;

    private array $numeric_types = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'bit', 'float', 'double', 'decimal'];

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

    public  ?object $foreign = null;

    public  ?Collection $has_many = null;

    function isPrimary(): bool {
        return $this->is_primary;
    }

    function isForeign(): bool {
        return $this->is_foreign;
    }

    function getHasMany(): ?Collection {
        return $this->has_many;
    }

    function getName(): string {
        return $this->name;
    }

    function getType(): string {
        return $this->type;
    }

    function isNullable(): bool {
        return $this->nullable;
    }

    function getMaxLength(): int {
        return $this->max_length;
    }

    function getCharMexLength(): int {
        return $this->char_max_length;
    }

    function getForeign(): ?object {
        return $this->foreign;
    }

    function getAllowedValues(): ?Collection {
        return $this->allowed_values;
    }

    function isEnum(): bool {
        return $this->type === 'enum';
    }

    function isUnSigned(): bool {
        return $this->unsigned;
    }

    /**
     * if the data is a textual data type
     */
    function isTextual(): bool {
        return in_array($this->type, ['varchar', 'char', 'smalltext', 'longtext', 'tinytext', 'text']);
    }

    /**
     * if the data is a float|double|decimal
     */

    function isFloat(): bool {
        return in_array($this->type, ['float', 'double', 'decimal']);
    }

    /**
     * if the data is an integer data type
     */
    function isInteger(): bool {
        return $this->type === 'int';
    }

    /**
     * if the data is an integer data type
     */
    function isBigInt(): bool {
        return $this->type === 'bigint';
    }

    public function __construct(string $table, $object) {
        $this->table = $table;

        $this->name = $object->COLUMN_NAME;

        $this->type = $object->DATA_TYPE;

        $this->default_value = $object->COLUMN_DEFAULT;

        $this->char_max_length = $object->CHARACTER_MAXIMUM_LENGTH;

        $this->max_length = intval(substr($object->COLUMN_TYPE, strpos($object->COLUMN_TYPE, '(') + 1, -1));

        $this->nullable = $object->IS_NULLABLE === 'YES';

        /* Numeric Values */
        $this->numeric_scale = $object->NUMERIC_SCALE;

        $this->numeric_precision = $object->NUMERIC_PRECISION;

        $this->increments = $object->EXTRA === 'auto_increment';

        $this->allowed_values = in_array($this->type, ['enum', 'set']) ? $this->extractValuesFromType($object->COLUMN_TYPE) : null;

        $this->unsigned = str_contains($object->COLUMN_TYPE, 'unsigned');

        /* Keys */
        $this->is_primary = $object->COLUMN_KEY === 'PRI';

        $this->is_foreign = $object->COLUMN_KEY === 'MUL';

        if ($this->is_foreign) $this->loadForeignKey();

        if ($this->is_primary) $this->loadHasManyRelations();
    }

    /**
     * Sets the foreign key
     */
    private function loadForeignKey(): void {
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
    private function loadHasManyRelations(): void {
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
     * Extract allowed values from 
     */
    private function extractValuesFromType(string $type): Collection {
        return str($type)
            ->substr(strpos($type, '(') + 2, -2)
            ->explode("','");

        // $found = false;
        // $values = [];
        // $value = '';

        // for ($i = 0; $i < strlen($this->allowed_values); $i++) {
        //     $char = $this->allowed_values[$i];
        //     if ($char === "'" && ($char !== "\\")) {
        //         $found = !$found;
        //         if ($i === (strlen($this->allowed_values) - 1)) {
        //             $values[] = $value;
        //             $value = '';
        //         }
        //     } elseif ($found) {
        //         $value .= $char;
        //         var_dump($char);
        //     } else {
        //         $values[] = $value;
        //         $value = '';
        //     }
        // }

    }
}

<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Database\Table;

class TypescriptGenerator {
    private Table $table;

    /** @var Collection $types  */
    private Collection $types;

    private Collection $enums;

    private array $numeric_types = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'bit', 'float', 'double', 'decimal'];

    private array $date_types = ['timestamp', 'date', 'datetime', 'time'];

    private array $binary_types = ['binary', 'varbinary', 'blob'];

    public function __construct(Table $table) {
        $this->table = $table;

        $this->types = collect();

        $this->enums = collect();

        $this->table->getColumns()->each(function (Column $column) {

            if ($this->typescriptType($column) === 'enum') {

                $this->enums->add($column);

                $this->types->put($column->getName(), [
                    'type' => str()->ucfirst(str()->camel($column->getName())),
                    'nullable' => $column->isNullable(),
                ]);
            } else {

                $this->types->put(str()->camel($column->getName()), [
                    'type' => $this->typescriptType($column),
                    'nullable' => $column->isNullable(),
                ]);
            }
        });
    }

    /**
     * Determine the Type for Typescript
     * @param Column $column 
     * @return string
     */
    private function typescriptType(Column $column): string {
        if ($column->getType() === "tinyint" && $column->getMaxLength() === 1) return 'boolean';

        if ($column->getType() === "enum") return 'enum';

        if (in_array($column->getType(), $this->numeric_types)) return 'number';

        if (in_array($column->getType(), $this->date_types)) return 'Date';

        if (in_array($column->getType(), $this->binary_types)) return 'Buffer';

        return 'string';
    }

    /**
     * Generate the typescript interface for the table
     * @return string
     */
    private function interface(): string {
        $interface = "export interface " . $this->table->getModelName() . " {\n";

        $interface .= $this->types->reduce(function (string $acc,  $type, $key) {
            return $acc . "\t" . $key . ($type['nullable'] ? '?' : '') . ": " . $type["type"] . ";\n";
        }, '');

        $interface .= "}\n";

        $enums = "";

        if ($this->enums->isNotEmpty()) {
            $enums .= "\n";
            $enums .= $this->enums
                ->map(function (Column $column) {
                    return EnumGenerator::typescript($column->getName(), $column->getAllowedValues());
                })
                ->implode("\n");
        }

        return $interface . $enums;
    }

    /**
     * Creates the Folder for types
     */
    public function ensureFolderExists(): void {
        if (!is_dir(base_path('types'))) mkdir(base_path('types'));
    }

    /**
     * Generates the typescript file
     */
    public function generateFile(string $file_name = 'index'): void {
        // $this->ensureFolderExists();
        File::append(base_path('types') . '/' . $file_name . '.ts', $this->interface());
    }

    /**
     * Generates Interfaces for multiple tables
     * @param Collection<Table> $tables
     * @return string
     */
    static function interfaces($tables) {

        $content = $tables
            ->map(fn ($table) => (new self($table))->interface())
            ->implode("\n");

        return $content;
    }

    /**
     * @param Collection<Table> $tables
     * @param string $file_name
     */
    static function generateAll($tables, $file_name = 'index') {
        if (!is_dir(base_path('types'))) mkdir(base_path('types'));
        File::put(base_path("types/$file_name.ts"), self::interfaces($tables));
    }
}

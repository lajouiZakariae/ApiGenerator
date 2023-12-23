<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Database\Table;

use function Laravel\Prompts\info;

class Generator {
    private Collection $generators;

    private static ?Collection $generator_instances = null;

    private bool $enums_generated = false;

    public function __construct(
        private Table $table
    ) {
        $this->generators = collect([
            'model'         => ModelGenerator::class,
            'controller'    => ControllerGenerator::class,
            'form_request'  => FormRequestGenerator::class,
            'resource'      => ResourceGenerator::class,
        ]);
    }

    private function hasEnumColumns(): bool {
        return $this->table->getColumns()->contains(fn (Column $column) => $column->isEnum());
    }

    private function generateEnumsIfNotExists(): void {
        if ($this->hasEnumColumns() && $this->enums_generated === false) {
            $this->enums();
            $this->enums_generated = true;
        };
    }

    public function model(): void {
        $model = new ModelGenerator($this->table);

        $model->loadData();

        $model->generateFile();

        $this->generateEnumsIfNotExists();
    }

    public function controller(): void {
        $controller = new ControllerGenerator($this->table);

        $controller->loadData();

        $controller->ensureFolderExists();

        $controller->generateFile();

        $this->formRequest();

        $this->resource();
    }

    public function formRequest(): void {
        $formRequest = new FormRequestGenerator($this->table);

        $formRequest->ensureFolderExists();

        $formRequest->loadData();

        $formRequest->generateFile();

        $this->generateEnumsIfNotExists();
    }

    public function resource(): void {
        $resource = new ResourceGenerator($this->table);

        $resource->ensureFolderExists();

        $resource->generateFile();
    }

    public function enums(): void {
        $enum_columns = $this->table->getColumns()->filter(fn (Column $column) => $column->isEnum());

        $enum_columns->isNotEmpty() ? EnumGenerator::generatePhpFiles($enum_columns) : null;
    }

    public function typescript() {
        $typescript = new TypescriptGenerator($this->table);
        $typescript->ensureFolderExists();
        $typescript->generateFile();
    }

    public function all(): void {
        $this->generators->each(function ($generator_class, $name) {
            $generator = new $generator_class($this->table);

            if (method_exists($generator_class, 'loadData')) $generator->loadData();

            if (method_exists($generator_class, 'ensureFolderExists')) $generator->ensureFolderExists();

            $generator->generateFile();

            info('Generating ' . $this->table->getName() . " " . str($name)->replace('_', ' ')->title());
        });

        $this->generateEnumsIfNotExists();
    }

    public static function table(Table|string $table): self {

        $table_name = $table instanceof Table ? $table->getName() : $table;

        if (!self::$generator_instances) self::$generator_instances = collect();

        if (!self::$generator_instances->has($table_name))
            self::$generator_instances->put(
                $table_name,
                new self($table instanceof Table ? $table : new Table($table_name))
            );

        return self::$generator_instances->get($table_name);
    }
}

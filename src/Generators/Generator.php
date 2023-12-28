<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Generators\FactoryGenerator;

use function Laravel\Prompts\info;

class Generator
{
    private Table $table;

    private Collection $generators;

    private static ?Collection $generator_instances = null;

    private bool $enums_generated = false;

    public function __construct(Table $table)
    {
        $this->table = $table;

        $this->generators = collect([
            'model'         => ModelGenerator::class,
            'controller'    => ControllerGenerator::class,
            'form_request'  => FormRequestGenerator::class,
            'resource'      => ResourceGenerator::class,
        ]);
    }

    private function hasEnumColumns(): bool
    {
        return $this->table->getColumns()->contains(fn (Column $column) => $column->isEnum());
    }

    private function generateEnumsIfNotExists(): void
    {
        if ($this->hasEnumColumns() && $this->enums_generated === false) {
            $this->enums();
            $this->enums_generated = true;
        };
    }

    public function model(bool $override = false): bool
    {
        $model = new ModelGenerator($this->table);

        $model->loadData();

        if (!$override && $model->fileExists()) return false;

        $model->generateFile();

        $this->generateEnumsIfNotExists();
        return true;
    }

    public function factory(bool $override = false): bool
    {
        $factory = new FactoryGenerator($this->table);

        $factory->loadData();

        if (!$override && $factory->fileExists()) return false;

        $factory->generateFile();

        $this->generateEnumsIfNotExists();
        return true;
    }

    public function controller(bool $override = false): bool
    {
        $controller = new ControllerGenerator($this->table);

        $controller->loadData();

        if (!$override && $controller->fileExists()) return false;

        $controller->ensureFolderExists();

        $controller->generateFile();

        $this->formRequest($override);

        $this->resource($override);

        return true;
    }

    public function formRequest(bool $override = false): bool
    {
        $formRequest = new FormRequestGenerator($this->table);

        if (!$override && $formRequest->fileExists()) return false;

        $formRequest->ensureFolderExists();

        $formRequest->loadData();

        $formRequest->generateFile();

        $this->generateEnumsIfNotExists();
        return true;
    }

    public function resource(bool $override = false): bool
    {
        $resource = new ResourceGenerator($this->table);

        $resource->loadData();

        if (!$override && $resource->fileExists()) return false;

        $resource->ensureFolderExists();

        $resource->generateFile();

        return true;
    }

    public function enums(): void
    {
        $enum_columns = $this->table->getColumns()->filter(fn (Column $column) => $column->isEnum());

        $enum_columns->isNotEmpty() ? EnumGenerator::generatePhpFiles($enum_columns) : null;
    }

    public function typescript()
    {
        $typescript = new TypescriptGenerator($this->table);

        $typescript->ensureFolderExists();

        $typescript->generateFile();
    }

    public function all(bool $override = false): void
    {
        $this->generators->each(function ($generator_class, $name) use ($override) {
            $generator = new $generator_class($this->table);

            if (method_exists($generator_class, 'loadData')) {
                $generator->loadData();
            }

            if ($override || !$generator->fileExists()) {

                if (method_exists($generator_class, 'ensureFolderExists')) {
                    $generator->ensureFolderExists();
                };

                $generator->generateFile();

                info('Generating ' . $this->table->getName() . " " . str($name)->replace('_', ' ')->title());
            };
        });

        $this->generateEnumsIfNotExists();
    }

    public static function table(Table|string $table): self
    {

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

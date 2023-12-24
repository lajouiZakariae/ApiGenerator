<?php

namespace Zakalajo\ApiGenerator\Generators;

use App\Enums\Type;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Table;

class FactoryGenerator {
    private Table $table;

    private ?string $factory_name;

    private string $model_name;

    private ?Collection $relations_models_imports = null;

    private ?Collection $columns_with_factories = null;

    public function __construct(Table $table) {
        $this->table = $table;

        $this->factory_name = $this->table->getFactoryName();

        $this->model_name = $this->table->getModelName();
    }

    /**
     * Loads neccessary data for the controller
     */
    function loadData(): void {
        $this->columns_with_factories = collect();

        $this->table->getColumns()->each(function (Column $column) {
            if (
                !$column->isForeign() && !$column->isPrimary() && !in_array($column->getName(), ['created_at', 'updated_at'])
            ) {
                $faker_string = 'fake()';

                if ($column->isNullable()) {
                    $faker_string .= '->optional()';
                }

                if ($column->getType() === 'text') {
                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string . "->text()"
                    );
                }

                if ($column->getType() === 'varchar') {
                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string . '->sentence()',
                    );
                }

                if ($column->isFloat()) {
                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string . '->randomFloat(max: 50000)',
                    );
                }

                if ($column->getType() === 'int') {
                    if ($column->isUnSigned()) {
                        $faker_string .= '->randomNumber()';
                    } else {
                        $faker_string .= '->randomDigit()';
                    }

                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string,
                    );
                }

                if ($column->getType() === 'tinyint' && $column->getMaxLength() === 1) {
                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string . '->boolean()',
                    );
                }

                if ($column->getType() === 'enum') {
                    $allowed_values = $column->getAllowedValues()->implode(
                        fn ($str) => "'" . $str . "'",
                        ','
                    );

                    $this->columns_with_factories->put(
                        "'" . $column->getName() . "'",
                        $faker_string . '->randomElement([' . $allowed_values . '])',
                    );
                }
            }
        });

        if ($this->table->getRelations()->has('belongs_to')) {
            $this->relations_models_imports = collect();

            $this->table->getRelations()->get('belongs_to')?->each(function ($relation) {
                $key = $relation->child_column;
                $value = str()->modelName($relation->parent_table) . '::factory()';

                $this->relations_models_imports->add("App\Models\\" . str()->modelName($relation->parent_table));
                $this->columns_with_factories->put("'$key'", $value);
            });
        }
    }

    /**
     * Chech File existence
     * @return bool
     **/
    public function fileExists(): bool {
        $path = base_path('database/factories/' . $this->table->getFactoryName() . '.php');

        return File::exists($path);
    }

    /**
     * Generates the controller file
     */
    function generateFile(): void {
        $content =
            view('apigenerator::factory', [
                "model_name" => $this->model_name,
                "model_import" => NamespaceResolver::modelImport($this->model_name),
                "factory_name" => $this->factory_name,
                "factory_namespace" => NamespaceResolver::factory(),
                "relations_models_imports" => $this->relations_models_imports,
                "columns_with_factories" => $this->columns_with_factories,
            ])->render();

        $path = base_path('database/factories/' . $this->table->getFactoryName() . '.php');

        File::put($path, $content);
    }
}

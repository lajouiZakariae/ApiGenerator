<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Database\Table;

class ModelGenerator {
    private Table $table;
    private string $model_name;
    private ?Collection $fillables = null;
    private ?Collection $casts = null;
    private ?Collection $has_many_relations = null;
    private ?Collection $belongs_to_relations = null;

    public function __construct(Table $table) {
        $this->table = $table;

        $this->model_name = $this->table->getModelName();
    }

    /**
     * Loads neccessary data for the model
     */
    public function loadData(bool $no_relations = false): void {
        $this->fillables = $this->table
            ->getColumns()
            ->filter(fn (Column $col) => !in_array($col->getName(), ['id', 'created_at', 'updated_at']))
            ->map(fn (Column $column) => $column->getName());

        $this->casts = collect();

        $this->table->getColumns()->each(function (Column $column) {
            if (in_array($column->getType(), ['float', 'double', 'decimal'])) {
                $this->casts->put("'" . $column->getName() . "'", "'float'");
            }

            if ($column->getType() === "tinyint" && $column->getMaxLength() === 1) {
                $this->casts->put("'" . $column->getName() . "'", "'boolean'");
            };

            if ($column->getType() === 'json') {
                $this->casts->put("'" . $column->getName() . "'", "'array'");
            }

            if ($column->getType() === 'enum') {
                $this->casts->put("'" . $column->getName() . "'", "\App\Enums\\" . str($column->getName())->camel()->ucfirst() . '::class');
            }
        });

        if (!$no_relations) {
            $this->has_many_relations = $this->table->getRelations()->has("has_many")
                ? $this->table->getRelations()->get("has_many")
                : null;

            $this->belongs_to_relations = $this->table->getRelations()->has('belongs_to')
                ? $this->table->getRelations()->get("belongs_to")
                : null;
        }
    }

    /**
     * Chech File existence
     * @return bool
     **/
    public function fileExists(): bool {
        $path = app_path('Models/' . $this->table->getModelName() . '.php');
        return File::exists($path);
    }

    /**
     * Generates the model file
     */
    function generateFile() {
        $content =
            view('apigenerator::model', [
                "name" => $this->model_name,
                'fillables' => $this->fillables,
                'casts' => $this->casts,
                'hasManyRelations' => $this->has_many_relations,
                'belongsToRelations' => $this->belongs_to_relations,
            ])->render();

        $path = app_path('Models/' . $this->table->getModelName() . '.php');
        File::put($path, $content);
    }
}

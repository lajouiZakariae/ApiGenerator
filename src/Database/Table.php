<?php

namespace Zakalajo\ApiGenerator\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Table {
    private string $name;

    private Column $primary_key;

    /** @var Collection<Column> $columns  */
    private $columns = null;

    /** @var Collection $relations  */
    private ?Collection $relations = null;

    function getColumns() {
        return $this->columns;
    }

    function getName() {
        return $this->name;
    }

    function getModelName() {
        return str()->modelName($this->name);
    }

    function getControllerName() {
        return $this->getModelName() . 'Controller';
    }

    function getResourceName() {
        return $this->getModelName() . 'Resource';
    }

    function getPostRequestName() {
        return $this->getModelName() . 'PostRequest';
    }

    function getFactoryName() {
        return $this->getModelName() . 'Factory';
    }

    function getRelations() {
        return $this->relations;
    }

    public function __construct(string $name) {
        $this->name = $name;
        $this->loadColumns();
        $this->loadPrimaryKey();
        $this->loadRelations();
    }

    function loadColumns() {
        $columns_result = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', $this->name)
            ->orderBy('ORDINAL_POSITION')
            ->get();

        $this->columns = $columns_result->map(fn ($obj) => new Column($this->name, $obj));
    }

    function loadPrimaryKey(): void {
        $this->primary_key = $this->columns->first(fn (Column $column) => $column->isPrimary());
    }

    function loadRelations(): void {

        $this->relations = collect();

        $this->relations->put('has_many', collect([]));
        $this->relations->put('belongs_to', collect([]));

        $this->columns->each(function (Column $column) {

            if ($column->getHasMany()) {

                $new_has_many = $this->relations->get('has_many');

                $column->getHasMany()->each(fn ($item) => $new_has_many = $new_has_many->add($item));
            }

            if ($column->isForeign()) {
                $new_belongs_to = $this->relations->get('belongs_to')->add($column->getForeign());

                $this->relations->put('belongs_to', $new_belongs_to);
            }
        });
    }
}

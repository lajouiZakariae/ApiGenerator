<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Database\Column;


class FormRequestGenerator implements IGenerator {
    private Table $table;

    private string $form_request_name;

    private Collection $validation_rules;

    private bool $should_import_rule_class = false;

    public function __construct(Table $table) {
        $this->table = $table;

        $this->form_request_name = $this->table->getPostRequestName();

        $this->validation_rules = collect();
    }

    /**
     * Loads neccessary data for the form request
     */
    function loadData(): void {
        $this->table
            ->getColumns()
            ->filter(fn (Column $column) => !in_array($column->getName(), ['id', 'created_at', 'updated_at']))
            ->each(function (Column $column) {
                $rules = collect([]);

                $column->isNullable() ? $rules->add("'nullable'") : $rules->add("'required'");

                if ($column->getType() === "tinyint" && $column->getMaxLength() === 1) // will stop here
                    return $rules->add('boolean');

                if ($column->getType() === 'enum') {
                    $this->should_import_rule_class = true;
                    return $rules->add("Rule::enum(\App\\Enums\\" . str()->ucfirst($column->getName()) . "::class)");
                }

                if ($column->isForeign()) {
                    return $rules->add("'exists:" . $column->getForeign()->parent_table . ',' . $column->getForeign()->parent_column . "'");
                }

                if ($column->isFloat()) {
                    $rules->add("'numeric'");
                }

                if ($column->isInteger()) {
                    $rules->add("'integer'");
                    $rules->add("'min:" . ($column->isUnSigned() ? 0 : -2_147_483_648) . "'");
                    $rules->add("'max:" . ($column->isUnSigned() ? 4_294_967_295 : 2_147_483_647) . "'");
                }

                if ($column->isBigInt()) {
                    $rules->add("'integer'");
                    $rules->add("'min:" . ($column->isUnSigned() ? 0 : -9_223_372_036_854_775_808) . "'");
                    $rules->add("'max:" . ($column->isUnSigned() ? 18_446_744_073_709_551_615 : 9_223_372_036_854_775_807) . "'");
                }

                if ($column->isTextual()) {
                    $rules->add("'string'");
                    $rules->add("'min:1'");
                    $rules->add("'max:" . $column->getCharMexLength() . "'");
                };

                $this->validation_rules->put($column->getName(), $rules);
            });
    }

    /**
     * Creates Form Requests folder
     */
    function ensureFolderExists(): void {
        if (!is_dir(app_path('Http/Requests'))) mkdir(app_path('Http/Requests'));

        $paths = NamespaceResolver::pathsOfFolders();

        $paths?->each(function (string $path): void {
            $directory_path = app_path('Http/Requests/' . $path);
            if (!is_dir($directory_path)) mkdir($directory_path);
        });
    }

    /**
     * Generates the form request file
     */
    function generateFile(): void {
        $stringified_rules = $this->validation_rules->isNotEmpty()
            ? $this->validation_rules->map(function (Collection $rules) {
                return "[" . $rules->implode(", ") . "]";
            })
            : null;

        $content =
            view('apigenerator::form_request', [
                "form_request_name" => $this->form_request_name,
                "form_request_namespace" => NamespaceResolver::formRequest(),
                "validation_rules" => $stringified_rules,
                'should_import_rule_class' => $this->should_import_rule_class,
            ])->render();

        File::put(app_path('Http/Requests/' . NamespaceResolver::getFolderPath() . "/" . $this->table->getPostRequestName() . '.php'), $content);
    }
}

<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Database\Column;


class FormRequestGenerator implements IGenerator
{
    private Table $table;

    private string $form_request_name;

    private Collection $validation_rules;

    private bool $should_import_rule_class = false;

    public function __construct(Table $table)
    {
        $this->table = $table;

        $this->form_request_name = $this->table->getPostRequestName();

        $this->validation_rules = collect();
    }

    /**
     * Loads neccessary data for the form request
     */
    function loadData(): void
    {
        $columns =
            $this->table
            ->getColumns()
            ->filter(fn (Column $column) => !in_array($column->getName(), ['id', 'created_at', 'updated_at']));

        $this->validation_rules = ValidationRules::resolveMany($columns);
    }

    /**
     * Chech File existence
     * @return bool
     **/
    public function fileExists(): bool
    {
        $path = app_path('Http/Requests/' . NamespaceResolver::getFolderPath() . "/" . $this->table->getPostRequestName() . '.php');

        return File::exists($path);
    }

    /**
     * Creates Form Requests folder
     */
    function ensureFolderExists(): void
    {
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
    function generateFile(): void
    {
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

        $path = app_path('Http/Requests/' . NamespaceResolver::getFolderPath() . "/" . $this->table->getPostRequestName() . '.php');

        File::put($path, $content);
    }
}

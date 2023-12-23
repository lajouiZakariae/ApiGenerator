<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Table;

class ControllerGenerator implements IGenerator {
    private Table $table;

    private string $controller_name;

    private ?string $model_name = null;

    private ?string $resource_name = null;

    private ?string $form_request_name = null;

    private ?Collection $belongs_to_relations = null;

    public function __construct(Table $table) {
        $this->table = $table;

        $this->controller_name = $this->table->getControllerName();
        $this->model_name = $this->table->getModelName();
    }

    /**
     * Loads neccessary data for the controller
     */
    function loadData(): void {
        $this->resource_name = $this->table->getResourceName();

        $this->form_request_name = $this->table->getPostRequestName();

        $this->belongs_to_relations = $this->table->getRelations()->get('belongs_to')?->map(function ($relation) {
            $method_name =  str($relation->parent_table)
                ->singular()
                ->append(str($relation->child_table)->camel()->ucfirst());

            return ((object)[
                'method_name' => $method_name,
                'parent_model_name' => str($relation->parent_table)->singular()->camel()->ucfirst(),
                'child_method_name' => str($relation->child_table)->camel(),
            ]);
        });
    }

    /**
     * Creates the Folder
     */
    function ensureFolderExists(): void {
        $paths = NamespaceResolver::pathsOfFolders();

        $paths?->each(function (string $path): void {
            $directory_path = app_path('Http/Controllers/' . $path);
            if (!is_dir($directory_path)) mkdir($directory_path);
        });
    }

    /**
     * Generates the controller file
     */
    function generateFile(): void {
        $content =
            view('apigenerator::controller', [
                "controller_name" => $this->controller_name,
                "controller_namespace" => NamespaceResolver::controller(),
                "model_name" => $this->model_name,
                "resource_name" => $this->resource_name,
                "form_request_name" => $this->form_request_name,
                "resource_import" => $this->resource_name
                    ? NamespaceResolver::resourceImport($this->resource_name) : null,
                "form_request_import" => $this->form_request_name
                    ? NamespaceResolver::formRequestImport($this->form_request_name) : null,
                "belongs_to_relations" => $this->belongs_to_relations
            ])->render();

        File::put(app_path('Http/Controllers/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getControllerName() . '.php'), $content);

        File::append(base_path('routes/api.php'), "\nRoute::apiResource(" . NamespaceResolver::controllerImport($this->table->getControllerName()) . "::class);\n");
    }
}

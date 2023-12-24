<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Table;

class FactoryGenerator implements IGenerator {
    private Table $table;

    private string $controller_name;

    private ?string $model_name = null;

    private ?string $resource_name = null;

    private ?string $form_request_name = null;

    private ?Collection $belongs_to_relations = null;

    /** @var ?Collection<string> $routes_as_strings */
    private $routes_as_strings = null;

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

        $this->form_request_name = $this->table->getFactoryName();

        if ($this->table->getRelations()->has('belongs_to')) {
        }
    }

    /**
     * Chech File existence
     * @return bool
     **/
    public function fileExists(): bool {
        $path = app_path('Http/Controllers/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getControllerName() . '.php');

        return File::exists($path);
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

    private function appendRoutes(): void {

        $table_name = $this->table->getName();
        $controller = NamespaceResolver::controllerImport($this->table->getControllerName()) . "::class";

        File::append(
            base_path('routes/api.php'),
            "\nRoute::apiResource('$table_name', $controller);\n\n"
        );

        $this->routes_as_strings?->each(
            fn (string $route_string) => File::append(base_path('routes/api.php'), $route_string)
        );
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

        $path = app_path('Http/Controllers/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getControllerName() . '.php');

        File::put($path, $content);

        $this->appendRoutes();
    }
}

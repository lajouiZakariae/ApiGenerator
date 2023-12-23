<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Interfaces\IGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;
use Zakalajo\ApiGenerator\Database\Column;
use Zakalajo\ApiGenerator\Database\Table;

class ResourceGenerator implements IGenerator {
    private Table $table;

    private string $model_name;

    private string $resource_name;

    private ?Collection $columns = null;

    public function __construct(Table $table) {
        $this->table = $table;
        $this->model_name = $this->table->getModelName();
        $this->resource_name = $this->table->getResourceName();
    }

    /**
     * Loads neccessary data for the resource
     */
    function loadData(): void {
        $this->columns =  $this->table->getColumns()->map(fn (Column $column) => (object)['name' => $column->getName()]);
    }

    /**
     * Creates the Folder
     */
    function ensureFolderExists(): void {
        if (!is_dir(app_path('Http/Resources'))) mkdir(app_path('Http/Resources'));

        $paths = NamespaceResolver::pathsOfFolders();

        $paths?->each(function (string $path): void {
            $directory_path = app_path('Http/Resources/' . $path);
            if (!is_dir($directory_path)) mkdir($directory_path);
        });
    }

    /**
     * Generates the resource file
     */
    function generateFile(): void {
        $content =
            view('apigenerator::resource', [
                "model_name" => $this->model_name,
                "resource_namespace" => NamespaceResolver::resource(),
                "resource_name" => $this->resource_name,
                "columns" => $this->columns
            ])->render();

        File::put(
            app_path('Http/Resources/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getResourceName() . '.php'),
            $content
        );
    }
}

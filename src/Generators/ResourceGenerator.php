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
    public function loadData(): void {
        $this->columns =  $this->table->getColumns()->map(fn (Column $column) => (object)['name' => $column->getName()]);
    }

    /**
     * Chech File existence
     * @return bool
     **/
    public function fileExists(): bool {
        $path = app_path('Http/Resources/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getResourceName() . '.php');

        return File::exists($path);
    }

    /**
     * Creates the Folder
     */
    public function ensureFolderExists(): void {
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
    public function generateFile(): void {
        $content =
            view('apigenerator::resource', [
                "model_name" => $this->model_name,
                "resource_namespace" => NamespaceResolver::resource(),
                "resource_name" => $this->resource_name,
                "columns" => $this->columns
            ])->render();

        $path = app_path('Http/Resources/' . NamespaceResolver::getFolderPath() . '/' . $this->table->getResourceName() . '.php');

        File::put($path, $content);
    }
}

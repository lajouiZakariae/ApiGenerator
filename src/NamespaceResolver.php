<?php

namespace Zakalajo\ApiGenerator;

use Illuminate\Support\Collection;

class NamespaceResolver {
    private static ?string $folder = null;

    private static string $controllers_namespace = 'App\Http\Controllers';

    private static string $models_namespace = 'App\Models';

    private static string $form_requests_namespace = 'App\Http\Requests';

    private static string $resources_namespace = 'App\Http\Resources';

    private static string $factories_namespace = 'Database\Factories';

    /**
     * Sets Folder Relative path with uppercase first for each folder
     * @param string $folder_name
     */
    static function setFolder(string $folder_name): void {
        self::$folder = str($folder_name)
            ->explode('/')
            ->implode(fn (string $name) => str()->ucfirst($name), "/");
    }

    /**
     * Get Folder Relative Path
     * @return string
     */
    static function getFolderPath(): string {
        return self::$folder ? self::$folder : '';
    }

    /**
     * Get Namespace version of folder
     * @return string
     */
    private static function getFolderNamespace(): string {
        return self::$folder
            ? '\\' . str(self::$folder)->replace('/', '\\')
            : '';
    }

    /**
     * Paths of Folders that sould be created
     * @return ?Collection
     */
    static function pathsOfFolders(): ?Collection {
        if (!self::$folder) return null;

        $folders = str(self::$folder)->explode('/');

        $folders_paths = collect();

        $folders->each(function ($_, $index) use ($folders_paths, $folders) {
            $path = $folders->slice(0, $index + 1)->implode('/');
            $folders_paths->add($path);
        });

        return $folders_paths;
    }

    /**
     * Models Full Namespace
     */
    static function model(): string {
        return self::$models_namespace;
    }

    /**
     * Full Namespace of model
     */
    static function modelImport(string $name): string {
        return self::model() . '\\' . $name;
    }

    /**
     * Controllers Full Namespace
     * @param string $name
     */
    static function controller(): string {
        return self::$controllers_namespace . self::getFolderNamespace();
    }


    /**
     * Full Namespace of controller
     * @param string $name
     */
    static function controllerImport(string $name): string {
        return self::controller() . '\\' . $name;
    }

    /**
     * Resources Full Namespace
     */
    static function resource(): string {
        return self::$resources_namespace . self::getFolderNamespace();
    }

    /**
     * Full Namespace of resource
     * @param string $name
     */
    static function resourceImport(string $name): string {
        return self::resource() . '\\' . $name;
    }

    /**
     * Form Requests Full Namespace
     */
    static function formRequest(): string {
        return self::$form_requests_namespace . self::getFolderNamespace();
    }

    /**
     * Full Namespace of form request
     * @param string $name
     */
    static function formRequestImport(string $name): string {
        return self::formRequest() . '\\' . $name;
    }


    /**
     * Models Full Namespace
     */
    static function factory(): string {
        return self::$factories_namespace;
    }

    /**
     * Full Namespace of factory
     */
    static function factoryImport(string $name): string {
        return self::factory() . '\\' . $name;
    }
}

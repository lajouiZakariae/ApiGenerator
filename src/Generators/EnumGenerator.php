<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Zakalajo\ApiGenerator\Database\Column;


class EnumGenerator
{
    protected string $name;

    protected Collection $values;

    protected Column $column;

    public function __construct(string $name, Collection $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * get the name
     * @return string
     */
    public function getName(): string
    {
        return str($this->name)->camel()->ucfirst();
    }

    /**
     * Typescript Enum
     * @param string $name 
     * @param Collection $values
     * @return string
     */
    function toTypescript(): string
    {
        $enum = "export enum " . $this->getName() . " {\n";

        $enum .= $this->values
            ->map(fn ($value) => "\t" . str($value)->snake()->upper() . " = \"" . $value . "\",\n")
            ->implode("");

        return $enum . "}\n";
    }

    /**
     * Php Enum
     * @param string $name 
     * @param Collection $values
     * @return string
     */
    function toPhp(): string
    {
        $enum = "enum " . $this->getName() . " {\n";

        $enum .= $this->values
            ->map(
                fn ($value) => "\tcase " . str($value)->snake()->upper() . " = \"" . $value . "\";\n"
            )->implode("\n");

        return $enum . "}\n";
    }

    /**
     * Typescript Enum
     * @param string $name 
     * @param Collection $values
     * @return string
     */
    public static function typescript(string $name, Collection $values): string
    {
        $instance = new self($name, $values);
        return $instance->toTypescript();
    }

    /**
     * Php Enum
     * @param string $name 
     * @param Collection $values
     * @return string
     */
    public static function php(string $name, Collection $values)
    {
        $instance = new self($name, $values);
        return $instance->toPhp();
    }

    /**
     * Generates Enum File
     */
    public static function generatePhpFile(string $name, Collection $values)
    {
        if (!is_dir(app_path('Enums'))) mkdir(app_path('Enums'));

        $instance = new self($name, $values);

        $name = $instance->getName();

        $content = "<?php\n\n";
        $content .= "namespace App\Enums;\n\n";
        $content .= $instance->toPhp();

        File::put(app_path("Enums/$name.php"), $content);
    }

    /**
     *  Generates Enum file for each column
     * @param Collection<Column> $columns Description
     * @return void
     **/
    public static function generatePhpFiles($columns)
    {
        $columns->each(function (Column $column) {
            self::generatePhpFile(
                $column->getName(),
                $column->getAllowedValues()
            );
        });
    }
}

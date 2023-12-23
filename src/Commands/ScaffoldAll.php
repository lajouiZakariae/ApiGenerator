<?php

namespace Zakalajo\ApiGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Zakalajo\ApiGenerator\Database\DBScanner;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Generators\ControllerGenerator;
use Zakalajo\ApiGenerator\Generators\EnumGenerator;
use Zakalajo\ApiGenerator\Generators\FormRequestGenerator;
use Zakalajo\ApiGenerator\Generators\Generator;
use Zakalajo\ApiGenerator\Generators\ModelGenerator;
use Zakalajo\ApiGenerator\Generators\ResourceGenerator;
use Zakalajo\ApiGenerator\Generators\TypescriptGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;

class ScaffoldAll extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaff:api  {folder?} {--types} {--enums}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an api';

    /**
     * Execute the console command.
     */
    public function handle() {
        NamespaceResolver::setFolder('admin');

        if ($this->option('types')) {
            $tables = DBScanner::database(env('DB_DATABASE'))->getTables();

            TypescriptGenerator::generateAll($tables);
        } elseif ($this->option('enums')) {
            $enum_columns = DBScanner::database(env('DB_DATABASE'))->getEnumColumns();

            EnumGenerator::generatePhpFiles($enum_columns);
        }

        $tables = DBScanner::database(env('DB_DATABASE'))->getTables();

        $tables->each(
            fn (Table|string $table) => Generator::table($table)->all()
        );

        TypescriptGenerator::generateAll($tables);
    }
}

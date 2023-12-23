<?php

namespace Zakalajo\ApiGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use Zakalajo\ApiGenerator\Database\DBScanner;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Generators\EnumGenerator;
use Zakalajo\ApiGenerator\Generators\Generator;
use Zakalajo\ApiGenerator\Generators\TypescriptGenerator;
use Zakalajo\ApiGenerator\NamespaceResolver;

class Scaffold extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaff:api  {--dir=} {--types} {--enums}';

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

        $this->option('dir') && str($this->option('dir'))->isNotEmpty()
            ? NamespaceResolver::setFolder($this->option('dir'))
            : null;

        if ($this->option('types')) {
            $tables = DBScanner::database(env('DB_DATABASE'))->getTables();

            TypescriptGenerator::generateAll($tables);
        } elseif ($this->option('enums')) {
            $enum_columns = DBScanner::database(env('DB_DATABASE'))->getEnumColumns();

            EnumGenerator::generatePhpFiles($enum_columns);
        } else {
            $tables = DBScanner::database(env('DB_DATABASE'))->getTables();

            $tables->each(
                fn (Table|string $table) => Generator::table($table)->all()
            );

            TypescriptGenerator::generateAll($tables);
        }
    }
}

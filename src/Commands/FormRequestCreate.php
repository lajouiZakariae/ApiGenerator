<?php

namespace Zakalajo\ApiGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Zakalajo\ApiGenerator\Database\DBScanner;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Generators\Generator;
use Zakalajo\ApiGenerator\NamespaceResolver;

class FormRequestCreate extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaff:request {table?} {--all} {--o|override}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new form request';

    /**
     * Execute the console command.
     */
    public function handle() {
        $table_name = $this->argument('table');

        $this->option('dir') && str($this->option('dir'))->isNotEmpty()
            ? NamespaceResolver::setFolder($this->option('dir'))
            : null;

        if ($table_name && str($table_name)->isNotEmpty()) {

            if (!Schema::hasTable($table_name)) {
                return $this->error('Table Does not exists');
            }

            if (!Generator::table($table_name)->formRequest(override: $this->option('override'))) {
                $this->warn('Request Already Exists');
            }
        } elseif ($this->option('all')) {

            DBScanner::database(env('DB_DATABASE'))
                ->getTables()
                ->each(
                    fn (Table $table) => Generator::table($table)->formRequest()
                );
        } else {
            $this->warn('Please Provide an option or a table name');
        }
    }
}

<?php

namespace Zakalajo\ApiGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Zakalajo\ApiGenerator\Database\DBScanner;
use Zakalajo\ApiGenerator\Database\Table;
use Zakalajo\ApiGenerator\Generators\Generator;

use function PHPSTORM_META\override;

class ResourceCreate extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaff:resource {table?} {--all} {--o|override}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        $table_name = $this->argument('table');

        if ($table_name && str($table_name)->isNotEmpty()) {
            if (!Schema::hasTable($table_name)) {
                return $this->error('Table Does not exists');
            }

            if (!Generator::table($table_name)->resource(override: $this->option('override'))) {
                $this->warn('Resource Already Exists');
            }
        } elseif ($this->option('all')) {
            DBScanner::database(env('DB_DATABASE'))
                ->getTables()
                ->each(
                    fn (Table $table) => Generator::table($table)->resource()
                );
        } else {
            $this->warn('Please Provide an option or a table name');
        }
    }
}

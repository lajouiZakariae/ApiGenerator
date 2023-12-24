<?php

namespace Zakalajo\ApiGenerator\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DBScanner {

    private string $database;

    /** @var Collection<Table> $tables */
    private Collection $tables;

    private array $ignored_tables = ["users", 'failed_jobs', 'migrations', 'personal_access_tokens', 'password_reset_tokens'];

    public function __construct(string $database) {
        $this->database = $database;

        $this->tables = DB::table('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_SCHEMA', $this->database)
            ->pluck('TABLE_NAME')
            ->diff(collect($this->ignored_tables))
            ->map(fn ($table) => new Table($table));
    }

    function getTables() {
        return $this->tables;
    }

    /**
     * @return Collection<Column>
     */
    function getEnumColumns() {
        return $this
            ->getTables()
            ->map(
                fn (Table $table) => $table
                    ->getColumns()
                    ->filter(fn (Column $column) => $column->isEnum())
            )
            ->flatten();
    }

    static function database(string $name): self {
        return new self($name);
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Hyperf\Database\OdbcSqlServer\Schema;

use Hyperf\Database\Exception\InvalidArgumentException;
use Hyperf\Database\Schema\Builder as Base;

class Builder extends Base
{
    /**
     * Create a database in the schema.
     */
    public function createDatabase(string $name): bool
    {
        return $this->connection->statement(
            $this->grammar->compileCreateDatabase($name, $this->connection)
        );
    }

    /**
     * Drop a database from the schema if the database exists.
     */
    public function dropDatabaseIfExists(string $name): bool
    {
        return $this->connection->statement(
            $this->grammar->compileDropDatabaseIfExists($name)
        );
    }

    /**
     * Drop all tables from the database.
     */
    public function dropAllTables(): void
    {
        $this->connection->statement($this->grammar->compileDropAllForeignKeys());

        $this->connection->statement($this->grammar->compileDropAllTables());
    }

    /**
     * Drop all views from the database.
     */
    public function dropAllViews(): void
    {
        $this->connection->statement($this->grammar->compileDropAllViews());
    }

    /**
     * Get the indexes for a given table.
     */
    public function getIndexes(string $table): array
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix() . $table;

        return $this->connection->getPostProcessor()->processIndexes(
            $this->connection->selectFromWriteConnection($this->grammar->compileIndexes($schema, $table))
        );
    }

    /**
     * Get the foreign keys for a given table.
     */
    public function getForeignKeys(string $table): array
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix() . $table;

        return $this->connection->getPostProcessor()->processForeignKeys(
            $this->connection->selectFromWriteConnection($this->grammar->compileForeignKeys($schema, $table))
        );
    }

    public function getColumnListing($table): array
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);

        $databaseName = $this->connection->getDatabaseName();

        $table = $this->connection->getTablePrefix() . $table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing($table),
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Determine if the given table has a given column.
     *
     * @param string $table
     * @param string $column
     */
    public function hasColumn($table, $column): bool
    {
        return in_array(strtolower($column), array_map('strtolower', $this->getColumnListing($table)), true);
    }

    /**
     * Parse the database object reference and extract the schema and table.
     */
    protected function parseSchemaAndTable(string $reference): array
    {
        $parts = array_pad(explode('.', $reference, 2), -2, 'dbo');

        if (str_contains($parts[1], '.')) {
            $database = $parts[0];

            throw new InvalidArgumentException("Using three-part reference is not supported, you may use `Schema::connection('{$database}')` instead.");
        }

        return $parts;
    }
}

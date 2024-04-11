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

namespace Hyperf\Database\OdbcSqlServer;

use Hyperf\Database\Connection as Base;
use Hyperf\Database\OdbcSqlServer\Query\Builder as QueryBuilder;
use Hyperf\Database\OdbcSqlServer\Query\Grammars\Grammar as QueryGrammar;
use Hyperf\Database\OdbcSqlServer\Query\Processors\Processor;
use Hyperf\Database\OdbcSqlServer\Schema\Builder;
use Hyperf\Database\OdbcSqlServer\Schema\Grammars\Grammar as SchemaGrammar;

class Connection extends Base
{
    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): Builder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }

    /**
     * Get a new query builder instance.
     */
    public function query(): QueryBuilder
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param string $value
     */
    protected function escapeBinary($value): string
    {
        $hex = bin2hex($value);

        return "0x{$hex}";
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        return $this->withTablePrefix(new SchemaGrammar());
    }

    /**
     * Get the default post processor instance.
     */
    protected function getDefaultPostProcessor(): Processor
    {
        return new Processor();
    }
}

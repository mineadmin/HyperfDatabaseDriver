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

namespace Hyperf\Database\Tests\OdbcSqlServer;

use Hyperf\Database\OdbcSqlServer\Query\Builder;
use Hyperf\Database\OdbcSqlServer\Query\Grammars\Grammar;
use Hyperf\DbConnection\Connection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSqlServerQueryGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testToRawSql()
    {
        $connection = m::mock(Connection::class);
        $connection->allows('escape')->with('foo', false)->andReturns("'foo'");
        $grammar = new Grammar();

        $bindings = array_map(fn ($value) => $connection->escape($value, false), ['foo']);

        $query = $grammar->substituteBindingsIntoRawSql(
            "select * from [users] where 'Hello''World?' IS NOT NULL AND [email] = ?",
            $bindings,
        );

        $this->assertSame("select * from [users] where 'Hello''World?' IS NOT NULL AND [email] = 'foo'", $query);
    }

    public function testCompileTruncate(): void
    {
        $reflection = new \ReflectionClass(Grammar::class);
        $instance = m::mock(Grammar::class);
        $method = $reflection->getMethod('compileTruncate');
        $instance->allows('wrapTable')->andReturnUsing(fn ($value) => $value);
        $query = m::mock(Builder::class);
        $query->from = 'users';
        $result = $method->invoke($instance, $query);
        $this->assertIsArray($result);
        $this->assertSame('truncate table users', array_keys($result)[0]);
        $this->assertSame([], array_values($result)[0]);
    }
}

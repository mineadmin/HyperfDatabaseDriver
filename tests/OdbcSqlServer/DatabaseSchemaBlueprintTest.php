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

use Hyperf\Database\Connection;
use Hyperf\Database\OdbcSqlServer\Schema\Grammars\Grammar;
use Hyperf\Database\Schema\Blueprint;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSchemaBlueprintTest extends TestCase
{
    public function testDefaultCurrentDateTime()
    {
        $base = new Blueprint('users', function ($table) {
            $table->dateTime('created')->useCurrent();
        });

        $connection = m::mock(Connection::class);

        $blueprint = clone $base;
        $this->assertEquals(['alter table "users" add "created" datetime not null default CURRENT_TIMESTAMP'], $blueprint->toSql($connection, new Grammar()));
    }

    public function testDefaultCurrentTimestamp()
    {
        $base = new Blueprint('users', function ($table) {
            $table->timestamp('created')->useCurrent();
        });

        $connection = m::mock(Connection::class);

        $blueprint = clone $base;
        $this->assertEquals(['alter table "users" add "created" datetime not null default CURRENT_TIMESTAMP'], $blueprint->toSql($connection, new Grammar()));
    }

    public function testRenameColumnWithoutDoctrine()
    {
        $base = new Blueprint('users', function ($table) {
            $table->renameColumn('foo', 'bar');
        });

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('usingNativeSchemaOperations')->andReturn(true);

        $blueprint = clone $base;
        $this->assertEquals(['sp_rename \'"users"."foo"\', "bar", \'COLUMN\''], $blueprint->toSql($connection, new Grammar()));
    }

    public function testDropColumnWithoutDoctrine()
    {
        $base = new Blueprint('users', function ($table) {
            $table->dropColumn('foo');
        });

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('usingNativeSchemaOperations')->andReturn(true);

        $blueprint = clone $base;
        $this->assertStringContainsString('alter table "users" drop column "foo"', $blueprint->toSql($connection, new Grammar())[0]);
    }

    public function testTinyTextColumn()
    {
        $base = new Blueprint('posts', function ($table) {
            $table->addColumn('tinyText', 'note');
        });

        $connection = m::mock(Connection::class);

        $blueprint = clone $base;
        $this->assertEquals([
            'alter table "posts" add "note" nvarchar(255) not null',
        ], $blueprint->toSql($connection, new Grammar()));
    }

    public function testTinyTextNullableColumn()
    {
        $base = new Blueprint('posts', function ($table) {
            $table->addColumn('tinyText', 'note')->nullable();
        });

        $connection = m::mock(Connection::class);

        $blueprint = clone $base;
        $this->assertEquals([
            'alter table "posts" add "note" nvarchar(255) null',
        ], $blueprint->toSql($connection, new Grammar()));
    }
}

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

use Hyperf\Database\OdbcSqlServer\Connectors\SqlServerConnector;
use Hyperf\Database\OdbcSqlServer\Exception\InvalidDriverException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseConnectorTest extends TestCase
{
    public function testSqlServerConnectCallsCreateConnectionWithInvalidDriver(): void
    {
        $this->expectException(InvalidDriverException::class);

        $config = ['host' => 'foo', 'database' => 'bar', 'port' => 111];
        $connector = $this->getMockBuilder(SqlServerConnector::class)->onlyMethods(['createConnection'])->getMock();
        $connector->connect($config);
    }

    public function testSqlServerConnectCallsCreateConnectionWithPreferredODBC(): void
    {
        $config = ['odbc' => true, 'odbc_datasource_name' => 'server=localhost;database=test;'];
        $dsn = $this->getDsn($config);
        $connector = $this->getMockBuilder(SqlServerConnector::class)
            ->onlyMethods(['createConnection', 'getOptions'])->getMock();
        $connection = m::mock(\PDO::class);
        $connector->expects($this->once())->method('getOptions')->with($this->equalTo($config))->willReturn(['options']);
        $connector->expects($this->once())->method('createConnection')->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options']))->willReturn($connection);
        $result = $connector->connect($config);

        $this->assertSame($result, $connection);
    }

    protected function getDsn(array $config): string
    {
        extract($config, EXTR_SKIP);

        $availableDrivers = \PDO::getAvailableDrivers();

        if (in_array('odbc', $availableDrivers, true)
            && ($config['odbc'] ?? null) === true) {
            return isset($config['odbc_datasource_name'])
                ? 'odbc:' . $config['odbc_datasource_name'] : '';
        }

        if (in_array('sqlsrv', $availableDrivers, true)) {
            $port = isset($config['port']) ? ',' . $port : '';
            $appname = isset($config['appname']) ? ';APP=' . $config['appname'] : '';
            $readonly = isset($config['readonly']) ? ';ApplicationIntent=ReadOnly' : '';
            $pooling = (isset($config['pooling']) && $config['pooling'] == false) ? ';ConnectionPooling=0' : '';

            return "sqlsrv:Server={$host}{$port};Database={$database}{$readonly}{$pooling}{$appname}";
        }
        $port = isset($config['port']) ? ':' . $port : '';
        $appname = isset($config['appname']) ? ';appname=' . $config['appname'] : '';
        $charset = isset($config['charset']) ? ';charset=' . $config['charset'] : '';

        return "dblib:host={$host}{$port};dbname={$database}{$charset}{$appname}";
    }
}

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

namespace Hyperf\Database\OdbcSqlServer\Connectors;

use Hyperf\Database\Connectors\Connector;
use Hyperf\Database\Connectors\ConnectorInterface;
use Hyperf\Database\OdbcSqlServer\Exception\InvalidDriverException;
use PDO;

class SqlServerConnector extends Connector implements ConnectorInterface
{
    /**
     * The PDO connection options.
     */
    protected array $options = [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     *
     * @throws \Exception
     */
    public function connect(array $config): \PDO
    {
        $options = $this->getOptions($config);

        $connection = $this->createConnection($this->getDsn($config), $config, $options);

        $this->configureIsolationLevel($connection, $config);

        return $connection;
    }

    /**
     * Set the connection transaction isolation level.
     *
     * https://learn.microsoft.com/en-us/sql/t-sql/statements/set-transaction-isolation-level-transact-sql
     */
    public function configureIsolationLevel(\PDO $connection, array $config): void
    {
        if (! isset($config['isolation_level'])) {
            return;
        }

        $connection->prepare(
            "SET TRANSACTION ISOLATION LEVEL {$config['isolation_level']}"
        )->execute();
    }

    /**
     * Create a DSN string from a configuration.
     */
    protected function getDsn(array $config): string
    {
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        if ($this->prefersOdbc()) {
            return $this->getOdbcDsn($config);
        }
        throw new InvalidDriverException('Coroutines processing is now only supported for pdo_odbc.');
    }

    /**
     * Determine if the database configuration prefers ODBC.
     */
    protected function prefersOdbc(): bool
    {
        return in_array('odbc', $this->getAvailableDrivers(), true);
    }

    /**
     * Get the DSN string for an ODBC connection.
     */
    protected function getOdbcDsn(array $config): string
    {
        return isset($config['odbc_datasource_name'])
            ? 'odbc:' . $config['odbc_datasource_name'] : '';
    }

    /**
     * Get the available PDO drivers.
     */
    protected function getAvailableDrivers(): array
    {
        return \PDO::getAvailableDrivers();
    }
}

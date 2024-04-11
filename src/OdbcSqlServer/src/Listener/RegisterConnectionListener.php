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

namespace Hyperf\Database\OdbcSqlServer\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\OdbcSqlServer\Connection as MssqlConnection;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterConnectionListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        Connection::resolverFor('odbc-sql-server', static function (
            $connection,
            string $database,
            string $prefix,
            array $config
        ) {
            return new MssqlConnection($connection, $database, $prefix, $config);
        });
    }
}

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

use Hyperf\Database\OdbcSqlServer\Connectors\SqlServerConnector;
use Hyperf\Database\OdbcSqlServer\Listener\RegisterConnectionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'db.connector.odbc-sql-server' => SqlServerConnector::class,
            ],
            'listeners' => [
                RegisterConnectionListener::class,
            ],
        ];
    }
}

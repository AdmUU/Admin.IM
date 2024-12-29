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

use App\Adm\Api\Middleware\SocketAuthMiddleware;
use Hyperf\Session\Middleware\SessionMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;

return [
    'http' => [
        ValidationMiddleware::class,
        SessionMiddleware::class,
    ],
    'socket-io' => [
        SessionMiddleware::class,
        SocketAuthMiddleware::class,
    ],
];

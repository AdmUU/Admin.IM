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

return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
    'SocketIOServer' => Hyperf\WebSocketServer\Server::class,
    Hyperf\SocketIOServer\SocketIO::class => App\Adm\Kernel\SocketIOFactory::class,
    Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
    App\Adm\Interfaces\AdmIpLocationInterface::class => App\Adm\Service\AdmIpLocationService::class,
];

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
use App\System\Middleware\WsAuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::get('/', function () {
    return 'welcome use mineAdmin';
});

Router::get('/favicon.ico', function () {
    return '';
});

// 消息ws服务器
Router::addServer('message', function () {
    Router::get('/message.io', 'App\System\Controller\ServerController', [
        'middleware' => [WsAuthMiddleware::class],
    ]);
});
// Router::get('/socket.io', 'App\Adm\Api\InterfaceApi\v1\SocketIOAgent', [
//     'middleware' => [SocketAuthMiddleware::class],
// ]);
// Router::addServer('socket-io', function () {
//     Router::get('/socket.io', 'App\Adm\Api\InterfaceApi\v1\SocketIOAgent', [
//         'middleware' => [SocketAuthMiddleware::class],
//     ]);
//     Router::get('/agent', 'App\Adm\Api\InterfaceApi\v1\SocketIOAgent', [
//         'middleware' => [SocketAuthMiddleware::class],
//     ]);
//     Router::get('/web', 'App\Adm\Api\InterfaceApi\v1\SocketIOWeb', [
//         'middleware' => [SocketAuthMiddleware::class],
//     ]);
// });

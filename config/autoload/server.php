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

use Hyperf\Framework\Bootstrap\PipeMessageCallback;
use Hyperf\Framework\Bootstrap\WorkerExitCallback;
use Hyperf\Framework\Bootstrap\WorkerStartCallback;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Mine\MineServer;
use Mine\MineStart;
use Swoole\Constant;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [MineServer::class, 'onRequest'],
            ],
            'settings' => [
                'open_websocket_protocol' => false,
            ],
        ],
        [
            'name' => 'message',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class, 'onHandShake'],
                Event::ON_MESSAGE => [Hyperf\WebSocketServer\Server::class, 'onMessage'],
                Event::ON_CLOSE => [Hyperf\WebSocketServer\Server::class, 'onClose'],
            ],
        ],
        [
            'name' => 'socket-io',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_HAND_SHAKE => ['SocketIOServer', 'onHandShake'],
                Event::ON_MESSAGE => ['SocketIOServer', 'onMessage'],
                Event::ON_CLOSE => ['SocketIOServer', 'onClose'],
            ],
            'settings' => [
                'heartbeat_idle_time' => 60,
                'heartbeat_check_interval' => 30,
            ],
        ],
    ],
    'settings' => [
        // 对外部可以直接访问的目录地址，建议使用nginx反向代理访问
        Constant::OPTION_DOCUMENT_ROOT => BASE_PATH . '/public',
        // 开启外部可以访问
        Constant::OPTION_ENABLE_STATIC_HANDLER => true,
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_MAX_COROUTINE => 100000,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_MAX_REQUEST => 0,
        Constant::OPTION_SOCKET_BUFFER_SIZE => 3 * 1024 * 1024,
        // 关闭buffer输出大小限制
        // Constant::OPTION_BUFFER_OUTPUT_SIZE     => 3 * 1024 * 1024,
        // 上传最大为4M
        Constant::OPTION_PACKAGE_MAX_LENGTH => 4 * 1024 * 1024,
        Constant::OPTION_RELOAD_ASYNC => true,
        Constant::OPTION_MAX_WAIT_TIME => 10,
    ],
    'callbacks' => [
        Event::ON_BEFORE_START => [MineStart::class, 'beforeStart'],
        Event::ON_WORKER_START => [WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [WorkerExitCallback::class, 'onWorkerExit'],
    ],
];

<?php

declare(strict_types=1);
/**
 * This file is part of Admin.IM
 *
 * @link     https://www.admin.im
 * @github   https://github.com/AdmUU/Admin.IM
 * @contact  dev@admin.im
 * @license  https://github.com/AdmUU/Admin.IM/blob/main/LICENSE
 */

namespace App\Adm\Api\SocketApi;

use App\Adm\Service\AdmNodeService;
use App\Adm\Service\AdmSocketService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

/**
 * SocketIO connection controller.
 */
#[SocketIONamespace('/')]
class SocketConnectApi extends BaseNamespace
{
    #[Inject]
    protected AdmSocketService $service;

    #[Inject]
    protected AdmNodeService $nodeService;

    #[Event('connect')]
    public function onConnect(Socket $socket): void
    {
        $this->service->connect($socket);
    }

    #[Event('disconnect')]
    public function onDisconnect(Socket $socket): void
    {
        $this->service->disconnect($socket);
    }
}

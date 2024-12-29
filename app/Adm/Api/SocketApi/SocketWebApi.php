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

use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmCommonService;
use App\Adm\Service\AdmNodeService;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\WebSocketServer\Context;

/**
 * SocketIO web controller.
 */
#[SocketIONamespace('/web')]
class SocketWebApi extends BaseNamespace
{
    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    protected AdmCommonService $commonService;

    #[Inject]
    protected AdmNodeService $nodeService;

    #[Event('web-task')]
    public function onWebTask(Socket $socket, $data)
    {
        $data = Json::decode($data);
        if (! isset($data['taskType']) || ! isset($data['taskId'])) {
            return $socket->emit('err', 'Missing parameters');
        }
        $taskType = $data['taskType'];
        $taskId = $data['taskId'];

        $task = $this->authService->getSocketTask(
            $taskType,
            $taskId
        );
        if (! $task) {
            $socket->emit('err', 'Invalid task');
            return $socket->disconnect();
        }
        if ($task['client_type'] != 'web') {
            $socket->emit('err', 'Invalid client_type');
            return $socket->disconnect();
        }
        if (! isset($task['class_name']) || ! isset($task['request_method'])) {
            $socket->emit('err', 'Invalid task class');
            return $socket->disconnect();
        }
        Context::set('task:id', $taskId);
        $this->commonService->callMethod($task['class_name'], $task['request_method'], [$socket, $task, $taskId]);
        Context::set('emitTask', true);
    }
}

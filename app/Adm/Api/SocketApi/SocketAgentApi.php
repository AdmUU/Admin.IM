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

use App\Adm\Api\Event\AgentEvent;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmCommonService;
use App\Adm\Service\AdmSocketService;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\WebSocketServer\Context;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * SocketIO agent controller.
 */
#[SocketIONamespace('/agent')]
class SocketAgentApi extends BaseNamespace
{
    #[Inject]
    protected AdmSocketService $service;

    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    protected AdmCommonService $commonService;

    #[Inject]
    protected EventDispatcherInterface $evDispatcher;

    #[Event('agent-task')]
    public function onAgentTask(Socket $socket, $data)
    {
        try {
            $data = Json::decode($data);
            $token = $data['token'];
            if (! $this->authService->checkSocketToken($token)) {
                $socket->emit('err', 'Invalid token');
                return $socket->disconnect();
            }
            Context::set('emitTask', true);
        } catch (\Throwable $e) {
            return $socket->emit('err', $e->getMessage());
        }
    }

    #[Event('agent-response')]
    public function onAgentResponse(Socket $socket, $data)
    {
        console()->debug($data);
        $data = Json::decode($data);
        if (! isset($data['res']['taskType']) || ! isset($data['res']['taskId'])) {
            return $socket->emit('err', 'Missing parameters');
        }
        $taskType = $data['res']['taskType'];
        $taskId = $data['res']['taskId'];

        $task = $this->authService->getSocketTask(
            $taskType,
            $taskId
        );
        if (! $task) {
            return $socket->emit('err', 'Invalid task');
        }
        if (! isset($task['class_name']) || ! isset($task['response_method'])) {
            return $socket->emit('err', 'Invalid task class');
        }
        $this->commonService->callMethod($task['class_name'], $task['response_method'], [$socket, $task, $data]);
    }

    #[Event('agent-keepalive')]
    public function onKeepAlive(Socket $socket): void
    {
        $this->service->agentKeepAlive($socket);
    }

    #[Event('agent-event')]
    public function onAgentEvent(Socket $socket, $data)
    {
        try {
            $data = Json::decode($data);
            if (! $data or ! isset($data['event'])) {
                return $socket->emit('err', 'Missing parameters');
            }
            $event = new AgentEvent($socket, $data);
            $this->evDispatcher->dispatch($event);
        } catch (\Throwable $e) {
            return $socket->emit('err', $e->getMessage());
        }
    }
}

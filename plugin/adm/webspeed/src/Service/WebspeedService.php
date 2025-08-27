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

namespace Plugin\Webspeed\Service;

use App\Adm\Interfaces\AdmIpLocationInterface;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmRequestStatisticsService;
use Hyperf\Context\Context as CoContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Socket;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Context;
use Mine\Abstracts\AbstractService;

/**
 * Webspeed Service.
 */
class WebspeedService extends AbstractService
{
    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    private AdmIpLocationInterface $ipLocation;

    #[Inject]
    private AdmRequestStatisticsService $requestStatistics;

    /**
     * Handle task request.
     */
    public function processRequestTask(Socket $socket, array $task, string $taskId): void
    {
        $content = $task['content'];
        $type = $task['type'];
        $sid = $socket->getSid();
        if (! in_array($type, ['quick', 'slow'])) {
            $socket->emit('err', 'Invalid webspeedtype');
            return;
        }

        $task['to'] = $sid;
        $this->authService->setSocketTask('webspeed', $task, $taskId);
        $requestData = [
            'content' => $content,
            'type' => $type,
            'taskId' => $taskId,
            'clientIP' => $task['client_ip'],
        ];

        $redis = redis();
        $snapNodeList = $redis->get($task['node_snapshot']);
        $nodeList = unserialize($snapNodeList);
        $io = container()->get(SocketIO::class);
        Coroutine::create(function () use ($nodeList, $redis, $io, $requestData) {
            $chunks = array_chunk($nodeList, 5);
            foreach ($chunks as $index => $chunk) {
                if ($index > 0) {
                    Coroutine::sleep(3);
                }
                foreach ($chunk as $nodeID) {
                    Coroutine::create(function () use ($nodeID, $redis, $io, $requestData) {
                        $nodeSid = $redis->hget('node:sid', (string) $nodeID);
                        if ($nodeSid) {
                            $io->of('/agent')->to($nodeSid)->emit('request-webspeed', $requestData);
                        }
                    });
                }
            }
        });
        Context::set('task:node_snapshot', $task['node_snapshot']);
        $this->requestStatistics->add('webspeed');
    }

    /**
     * Handle task response.
     */
    public function processResponseTask(Socket $socket, array $task, array $data): void
    {
        try {
            $to = $task['to'];
            $locale = $task['locale'];
            $res = $data['res'];
            $sid = $socket->getSid();
            $fd = CoContext::get('ws.fd', 0);
            $did = Context::get('socket:nodeDid', null, $fd);
            $responseData = ['httpCode' => '-1'];
            if (isset($res['httpCode'])) {
                $responseData = $res;
            } elseif (isset($res['ip'])) {
                $webspeedIP = $res['ip'];
                $responseData = [
                    'ip' => $webspeedIP,
                    'location' => $this->ipLocation->getName($res['ip'], $locale),
                ];
            }
            $responseData['sid'] = $sid;
            $responseData['did'] = $did;
            $io = container()->get(SocketIO::class);
            $io->of('/web')->to($to)->emit('response-webspeed', $responseData);
        } catch (\Throwable $e) {
            \exception_log($e);
            $socket->emit('err', $e->getMessage());
        }
    }
}

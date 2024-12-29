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

namespace Plugin\Ping\Service;

use App\Adm\Interfaces\AdmIpLocationInterface;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmRequestStatisticsService;
use Hyperf\Context\Context as CoContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Socket;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Context;
use Mine\Abstracts\AbstractService;

/**
 * Ping Service.
 */
class PingService extends AbstractService
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
        $host = $task['host'];
        $pingtype = $task['ping_type'];
        $protocol = $task['ping_protocol'];
        $sid = $socket->getSid();
        if (! in_array($pingtype, ['single', 'continuous'])) {
            $socket->emit('err', 'Invalid pingtype');
            return;
        }
        if (! in_array($protocol, ['icmp', 'tcp'])) {
            $socket->emit('err', 'Invalid protocol');
            return;
        }
        $task['to'] = $sid;
        $this->authService->setSocketTask('ping', $task, $taskId);
        $requestData = [
            'host' => $host,
            'pingtype' => $pingtype,
            'protocol' => $protocol,
            'taskId' => $taskId,
        ];

        $redis = redis();
        $snapNodeList = $redis->get($task['node_snapshot']);
        $nodeList = unserialize($snapNodeList);
        $io = container()->get(SocketIO::class);
        foreach ($nodeList as $nodeID) {
            $nodeSid = $redis->hget('node:sid', (string) $nodeID);
            $nodeSid && $io->of('/agent')->to($nodeSid)->emit('request-ping', $requestData);
        }
        Context::set('task:node_snapshot', $task['node_snapshot']);
        $this->requestStatistics->add('ping');
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
            $responseData = [];
            if (isset($res['delay'])) {
                $responseData = [
                    'delay' => $res['delay'],
                    'sid' => $sid,
                    'did' => $did,
                ];
            } elseif (isset($res['ip'])) {
                $pingIP = $res['ip'];
                if (isset($res['port']) && $res['port'] != '') {
                    $pingIP = $res['ipVersion'] == 'IPv6' ? '[' . $res['ip'] . ']' : $pingIP;
                    $pingIP = $pingIP . ':' . $res['port'];
                }
                $responseData = [
                    'ip' => $pingIP,
                    'location' => $this->ipLocation->getName($res['ip'], $locale),
                    'sid' => $sid,
                    'did' => $did,
                ];
            }
            $io = container()->get(SocketIO::class);
            $io->of('/web')->to($to)->emit('response-ping', $responseData);
        } catch (\Throwable $e) {
            \exception_log($e);
            $socket->emit('err', $e->getMessage());
        }
    }
}

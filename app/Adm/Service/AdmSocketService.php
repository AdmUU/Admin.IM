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

namespace App\Adm\Service;

use Hyperf\Context\Context as CoContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\SocketIOServer\Socket;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Context;
use Mine\Abstracts\AbstractService;

use function Hyperf\Coordinator\block;
use function Hyperf\Coordinator\resume;
use function Hyperf\Coroutine\co;

/**
 * Socket IO Service.
 */
class AdmSocketService extends AbstractService
{
    public static $waitConnections = [];

    #[Inject]
    protected AdmNodeService $nodeService;

    /**
     * Socket connect.
     */
    public function connect(Socket $socket): void
    {
        $fd = CoContext::get('ws.fd', 0);
        $sid = $socket->getSid();
        $socketType = Context::get('socket:type', null, $fd);
        if (! Context::has('connCheck')) {
            Context::set('connCheck', true);
            co(function () use ($socket, $sid, $fd, $socketType) {
                if (block(2)) {
                    $socket->to($sid)->disconnect();
                    return;
                }
                if (! Context::get('emitTask', null, $fd)) {
                    $socket->to($sid)->disconnect();
                } else {
                    $nodeID = Context::get('socket:nodeID', null, $fd);
                    if ($socketType == 'agent' && $nodeID) {
                        $this->nodeService->updateOnlineStatus((int) $nodeID, 1);
                        $redis = redis();
                        $redis->hset('node:sid', $nodeID, $sid);
                        $socket->emit('connect', $sid);
                        alog('Node ' . $nodeID . ' ' . $sid . ' ' . $fd . ' connect', 'info', 'Socket', null, true);
                    }
                }
            });
        }
        if ($socketType == 'web') {
            co(function () use ($socket, $sid, $fd) {
                $waitSid = $sid . '#' . $fd;
                self::$waitConnections[$waitSid] = $fd;
                if (block(300, 'conn.' . $waitSid)) {
                    if (isset(self::$waitConnections[$waitSid])) {
                        unset(self::$waitConnections[$waitSid]);
                        $socket->to($sid)->disconnect();
                    }
                    return;
                }
                $socket->to($sid)->disconnect();
            });
        } elseif ($socketType == 'agent') {
            co(function () use ($socket, $sid, $fd) {
                self::$waitConnections[$sid] = $fd;
                while (true) {
                    if (block(20, 'conn.' . $sid)) {
                        if (isset(self::$waitConnections[$sid])) {
                            unset(self::$waitConnections[$sid]);
                            $socket->to($sid)->disconnect();
                        }
                        break;
                    }
                    $nodeID = Context::get('socket:nodeID', null, $fd);
                    if ($nodeID === null) {
                        $socket->to($sid)->disconnect();
                        break;
                    }
                    $redis = redis();
                    if ($redis->sIsMember('node:disconnect', $sid)) {
                        $socket->to($sid)->disconnect();
                        $redis->sRem('node:disconnect', $sid);
                        break;
                    }
                }
            });
        }
    }

    /**
     * Socket disconnect.
     */
    public function disconnect(Socket $socket): void
    {
        $sid = $socket->getSid();
        $fd = CoContext::get('ws.fd', 0);
        $socketType = Context::get('socket:type', null, $fd);
        $taskID = Context::get('task:id', null, $fd);
        $taskNodeSnapshot = Context::get('task:node_snapshot', null, $fd);
        $waitSid = $sid;
        if ($socketType == 'web' && $taskID && $taskNodeSnapshot) {
            $redis = redis();
            $snapNodeList = $redis->get($taskNodeSnapshot);
            $nodeList = unserialize($snapNodeList);
            $io = container()->get(SocketIO::class);
            foreach ($nodeList as $nodeID) {
                $nodeSid = $redis->hget('node:sid', (string) $nodeID);
                $nodeSid && $io->of('/agent')->to($nodeSid)->emit('stop-task', $taskID);
            }
            $waitSid = $sid . '#' . $fd;
        }
        $nodeID = Context::get('socket:nodeID', null, $fd);
        if ($socketType == 'agent' && $nodeID) {
            $this->nodeService->updateOnlineStatus((int) $nodeID, 0);
            $redis = redis();
            $redis->hdel('node:connect', (string) $nodeID);
            $nodeSid = $redis->hget('node:sid', (string) $nodeID);
            if ($nodeSid) {
                $redis->srem('node:disconnect', $nodeSid);
            }
            $redis->hdel('node:sid', (string) $nodeID);
            alog('NodeID: ' . $nodeID . '  disconnect');
        }
        if (isset(self::$waitConnections[$waitSid])) {
            unset(self::$waitConnections[$waitSid]);
            resume('conn.' . $waitSid);
        }
    }

    /**
     * Reset connections when worker exit.
     */
    public static function resetConnect(): void
    {
        foreach (self::$waitConnections as $sid => $fd) {
            resume('conn.' . $sid);
        }
    }

    /**
     * Disconnect the nodes.
     */
    public function nodeDisconnect(array $nodeIDs, string $type = 'disable'): void
    {
        $redis = redis();
        $socket = container()->get(SocketIO::class);
        foreach ($nodeIDs as $nodeID) {
            $sid = $redis->hget('node:sid', (string) $nodeID);
            if ($sid) {
                $socket->of('/agent')->to($sid)->emit($type, 'Force disconnect');
                $redis->sAdd('node:disconnect', $sid);
            } else {
                $redis->hdel('node:connect', (string) $nodeID);
                $this->nodeService->updateOnlineStatus((int) $nodeID, 0);
            }
        }
    }

    /**
     * Agent keep alive.
     */
    public function agentKeepAlive(): void
    {
        $fd = CoContext::get('ws.fd', 0);
        $nodeID = (string) Context::get('socket:nodeID', null, $fd);
        $redis = redis();
        $lastAlive = $redis->hget('node:alive', $nodeID);
        if ($lastAlive && $lastAlive > 0) {
            $redis->hincrby('node:onlinetime', $nodeID, time() - $lastAlive);
        }
        $redis->hset('node:alive', $nodeID, time());
    }

    /**
     * Update the nodes.
     */
    public function nodeUpdate(array $nodeIDs): bool
    {
        $redis = redis();
        $socket = container()->get(SocketIO::class);
        $sid = null;
        foreach ($nodeIDs as $nodeID) {
            $sid = $redis->hget('node:sid', (string) $nodeID);
            if ($sid) {
                $socket->of('/agent')->to($sid)->emit('update', 'Self update');
            }
        }
        return ! empty($sid);
    }
}

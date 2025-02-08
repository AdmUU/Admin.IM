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

namespace App\Adm\Task;

use App\Adm\Service\AdmNodeService;
use App\Adm\Service\AdmSocketService;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * Crontab task for online status.
 */
#[Crontab(name: 'OnlineStatus', rule: '* * * * *', callback: 'execute', singleton: true, onOneServer: true, memo: 'Online Status')]
class OnlineStatusTask
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    #[Inject]
    protected AdmNodeService $service;

    /**
     * Execute the task.
     */
    public function execute(): void
    {
        $this->checkStatus();
    }

    /**
     * Check online status.
     * @param mixed $deleteKey
     */
    public function checkStatus($deleteKey = false): void
    {
        try {
            $redis = redis();
            $connectNodes = $redis->hgetall('node:connect');
            $cacheNodes = array_keys($connectNodes);
            $dbNodes = array_column($this->service->getNodeList(['id']), 'id');
            $nodeIDs = array_values(array_unique(array_merge($cacheNodes, $dbNodes)));
            if (count($nodeIDs) > 0) {
                $cacheSids = array_values($redis->hmget('node:sid', array_map('strval', $nodeIDs)));
                $expireAgents = $redis->zmscore('ws:/agent:expire', ...$cacheSids);
                foreach ($nodeIDs as $index => $nodeID) {
                    if ((! empty($expireAgents[$index]) && $expireAgents[$index] >= (microtime(true) - 1) * 1000)
                        || (isset($connectNodes[$nodeID]) && strtotime($connectNodes[$nodeID]) >= time() - 3)) {
                        unset($nodeIDs[$index]);
                    }
                }
                if (count($nodeIDs) > 0) {
                    // if ($deleteKey) {
                    foreach ($nodeIDs as $nodeID) {
                        $nodeSid = $redis->hGet('node:sid', (string) $nodeID);
                        if ($nodeSid) {
                            $redis->sRem('node:disconnect', $nodeSid);
                            $redis->hDel('node:sid', (string) $nodeID);
                        }
                    }
                    // }
                    $socketService = container()->get(AdmSocketService::class);
                    $socketService->nodeDisconnect($nodeIDs);
                    alog('Disconnect nodes by checkStatus: ' . implode(',', $nodeIDs), 'info', 'Crontab', null, true);
                }
            }
        } catch (\Exception $e) {
            logger('Task log')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            echo $e->getMessage();
        }
    }
}

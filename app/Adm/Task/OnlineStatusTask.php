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
#[Crontab(name: 'OnlineStatus', rule: '* * * * *', callback: 'execute', memo: 'Online Status')]
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
     */
    public function checkStatus(): void
    {
        try {
            $redis = redis();
            $cacheNodes = array_keys($redis->hgetall('node:connect'));
            $dbNodes = array_column($this->service->getNodeList(['id']), 'id');
            $nodeIDs = array_values(array_unique(array_merge($cacheNodes, $dbNodes)));
            if (count($nodeIDs) > 0) {
                $cacheSids = array_values($redis->hmget('node:sid', array_map('strval', $nodeIDs)));
                $expireAgents = $redis->zmscore('ws:/agent:expire', ...$cacheSids);
                foreach ($nodeIDs as $index => $nodeID) {
                    if (! empty($expireAgents[$index]) && $expireAgents[$index] >= time()) {
                        unset($nodeIDs[$index]);
                    }
                }
                if (count($nodeIDs) > 0) {
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

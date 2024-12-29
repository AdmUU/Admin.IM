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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * Crontab task for online time statistics.
 */
#[Crontab(name: 'OnlineTime', rule: '* * * * *', callback: 'execute', memo: 'Online Time')]
class OnlineTimeTask
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
        $this->statistics();
    }

    /**
     * Statistics online time.
     *
     * @param bool $deleteKey
     */
    public function statistics($deleteKey = false): void
    {
        try {
            $redis = redis();
            $nodes = array_keys($redis->hgetall('node:connect'));
            foreach ($nodes as $nodeID) {
                $lastAlive = $redis->hget('node:alive', (string) $nodeID);
                if ($lastAlive && $lastAlive > 1734278400) {
                    $duration = time() - $lastAlive;
                    if ($duration > 0) {
                        if (! $this->service->get((int) $nodeID)) {
                            continue;
                        }
                        $this->service->update($nodeID, ['online_total_time' => Db::raw('online_total_time + ' . $duration)]);
                    }
                }
                $redis->hset('node:alive', (string) $nodeID, time());
            }
            if ($deleteKey) {
                $redis->del('node:alive');
                $redis->del('node:connect');
            }
        } catch (\Exception $e) {
            logger('Task log')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            echo $e->getMessage();
        }
    }
}

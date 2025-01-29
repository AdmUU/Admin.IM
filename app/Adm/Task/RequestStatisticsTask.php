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

use App\Adm\Service\AdmRequestStatisticsService;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * Crontab task for request statistics.
 */
#[Crontab(name: 'RequestStatistics', rule: '* * * * *', callback: 'execute', singleton: true, onOneServer: true, memo: 'Request Statistics')]
class RequestStatisticsTask
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    #[Inject]
    protected AdmRequestStatisticsService $service;

    /**
     * Execute the task.
     */
    public function execute(): void
    {
        $this->statistics();
    }

    /**
     * Statistics request count.
     */
    public function statistics(): void
    {
        try {
            $this->service->saveRecord('ping');
        } catch (\Exception $e) {
            logger('Task log')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            echo $e->getMessage();
        }
    }
}

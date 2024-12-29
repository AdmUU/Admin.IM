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

namespace App\Adm\Listener;

use App\Adm\Service\AdmSocketService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerExit;

/**
 * Boot Listener.
 */
#[Listener]
class WorkerExitListener implements ListenerInterface
{
    /**
     * listen worker exit.
     */
    public function listen(): array
    {
        return [
            OnWorkerExit::class,
        ];
    }

    /**
     * process.
     */
    public function process(object $event): void
    {
        $this->resetSocketConnect();
    }

    /**
     * Reset socket connections.
     */
    private function resetSocketConnect(): void
    {
        make(AdmSocketService::class)->resetConnect();
    }
}

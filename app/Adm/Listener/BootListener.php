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

use App\Adm\Service\AdmNodeService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;

/**
 * Boot Listener.
 */
#[Listener]
class BootListener implements ListenerInterface
{
    /**
     * listen worker start.
     */
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * process.
     */
    public function process(object $event): void
    {
        $this->resetNodeStatus();
    }

    /**
     * Reset node status.
     */
    private function resetNodeStatus(): void
    {
        make(AdmNodeService::class)->resetNodeStatus();
    }
}

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

namespace Plugin\Ping\Request;

use Mine\MineApiFormRequest;

/**
 * Ping form request validation.
 */
class PingRequest extends MineApiFormRequest
{
    /**
     * Request task rules.
     */
    public function requestTaskRules(): array
    {
        return [
            'client_type' => 'required|string|in:web,agent',
            'ping_protocol' => 'required|string|in:icmp,tcp',
            'ping_type' => 'required|string|in:single,continuous',
            'host' => 'required|string',
            'isp' => 'string',
            'locale' => 'string',
        ];
    }
}

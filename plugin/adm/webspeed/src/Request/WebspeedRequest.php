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

namespace Plugin\Webspeed\Request;

use Mine\MineApiFormRequest;

/**
 * Webspeed form request validation.
 */
class WebspeedRequest extends MineApiFormRequest
{
    /**
     * Request task rules.
     */
    public function requestTaskRules(): array
    {
        return [
            'client_type' => 'required|string|in:web,agent',
            'type' => 'required|string|in:quick,slow',
            'content' => 'required|string',
            'isp' => 'string',
            'locale' => 'string',
        ];
    }
}

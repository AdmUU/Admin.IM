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

namespace App\Adm\Api\Request\v1;

use Mine\MineApiFormRequest;

/**
 * Api form request validation.
 */
class AdmApiRequest extends MineApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get node list rules.
     */
    public function getNodeListRules(): array
    {
        return [
            'prefer_ip_type' => 'string|in:ipv4,ipv6,dual',
            'dict_code' => 'string|in:region,isp',
            'dict_value' => 'string',
            'task_type' => 'string|in:ping,webspeed',
            'task_id' => 'string',
        ];
    }

    /**
     * Regist node rules.
     */
    public function registNodeRules(): array
    {
        return [
            'fingerprint' => 'string|size:64|required',
            'key' => 'string|size:8|required',
            'version' => 'string',
            'auth_code' => 'string',
            'ip' => 'ip',
            'ipv4' => 'ipv4',
            'ipv6' => 'ipv6',
            'sponsor' => 'string|between:1,8',
            'sponsor_id' => 'string|size:16',
            'sponsor_url' => 'string|size:50',
        ];
    }

    /**
     * Request agent token rules.
     */
    public function requestAgentTokenRules(): array
    {
        return [
            'auth_code' => 'required|string',
            'version' => 'string',
        ];
    }

    /**
     * Messages.
     */
    public function messages(): array
    {
        return [
            'ip_type.string' => 'ip type must string',
        ];
    }
}

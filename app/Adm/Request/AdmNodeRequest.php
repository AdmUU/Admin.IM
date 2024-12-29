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

namespace App\Adm\Request;

use Mine\MineFormRequest;

/**
 * Node form request validation.
 */
class AdmNodeRequest extends MineFormRequest
{
    /**
     * Common rules.
     */
    public function commonRules(): array
    {
        return [];
    }

    /**
     * New data rules.
     */
    public function saveRules(): array
    {
        return [
            'name' => 'required',
            'name_en' => 'required',
            'enable' => 'required',
            // 'country' => 'required',
            // 'region' => 'required',
            // 'continent' => 'required',
        ];
    }

    /**
     * Update data rules.
     */
    public function updateRules(): array
    {
        return [
            'name' => 'required',
            'name_en' => 'required',
            'enable' => 'required',
            // 'country' => 'required',
            // 'region' => 'required',
            // 'continent' => 'required',
        ];
    }

    /**
     * Attributes.
     */
    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'name' => '节点名称',
            'name_en' => '英文名称',
            'enable' => '启用',
            'country' => '国家地区',
            'region' => '区域',
            'isp' => '运营商',
            'provider' => '服务商',
            'continent' => '大洲',
            'online_status' => '在线',
        ];
    }
}

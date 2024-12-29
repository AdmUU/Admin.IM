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
 * Node group form request validation.
 */
class AdmNodeGroupRequest extends MineFormRequest
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
            'weight' => 'required',
        ];
    }

    /**
     * Attributes.
     */
    public function attributes(): array
    {
        return [
            'id' => '',
            'name' => '名称',
            'name_en' => '英文名称',
            'enable' => '启用',
            'weight' => '排序权重',
        ];
    }
}

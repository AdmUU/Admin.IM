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

namespace App\Adm\Dto;

use Mine\Annotation\ExcelData;
use Mine\Annotation\ExcelProperty;
use Mine\Interfaces\MineModelExcel;

/**
 * Node Grouping Dto (Import and Export).
 */
#[ExcelData]
class AdmNodeGroupDto implements MineModelExcel
{
    #[ExcelProperty(value: 'id', index: 0)]
    public string $id;

    #[ExcelProperty(value: '名称', index: 1)]
    public string $name;

    #[ExcelProperty(value: '英文名称', index: 2)]
    public string $name_en;

    #[ExcelProperty(value: '启用', index: 3)]
    public string $enable;

    #[ExcelProperty(value: '排序权重', index: 4)]
    public string $weight;

    #[ExcelProperty(value: 'Client ID', index: 5)]
    public string $client_id;

    #[ExcelProperty(value: 'Client Secret', index: 6)]
    public string $client_secret;

    #[ExcelProperty(value: '扩展信息', index: 7)]
    public string $ext;

    #[ExcelProperty(value: 'created_at', index: 8)]
    public string $created_at;

    #[ExcelProperty(value: 'updated_at', index: 9)]
    public string $updated_at;

    #[ExcelProperty(value: 'deleted_at', index: 10)]
    public string $deleted_at;
}

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
 * Node Management Dto (Import and Export).
 */
#[ExcelData]
class AdmNodeDto implements MineModelExcel
{
    #[ExcelProperty(value: 'ID', index: 0)]
    public string $id;

    #[ExcelProperty(value: 'Distributed ID', index: 1)]
    public string $did;

    #[ExcelProperty(value: '节点名称', index: 2)]
    public string $name;

    #[ExcelProperty(value: '英文名称', index: 3)]
    public string $name_en;

    #[ExcelProperty(value: '启用', index: 4)]
    public string $enable;

    #[ExcelProperty(value: 'IPv4', index: 5)]
    public string $ipv4;

    #[ExcelProperty(value: 'IPv6', index: 6)]
    public string $ipv6;

    #[ExcelProperty(value: '国家地区', index: 7)]
    public string $country;

    #[ExcelProperty(value: '省份', index: 8)]
    public string $province;

    #[ExcelProperty(value: '区域', index: 9)]
    public string $region;

    #[ExcelProperty(value: '大洲', index: 10)]
    public string $continent;

    #[ExcelProperty(value: '运营商', index: 11)]
    public string $isp;

    #[ExcelProperty(value: '在线', index: 12)]
    public string $online_status;

    #[ExcelProperty(value: '在线时长', index: 13)]
    public string $online_total_time;

    #[ExcelProperty(value: '最后在线时间', index: 14)]
    public string $online_last_time;

    #[ExcelProperty(value: '排序权重', index: 15)]
    public string $weight;

    #[ExcelProperty(value: '版本号', index: 16)]
    public string $version;

    #[ExcelProperty(value: '连接类型', index: 17)]
    public string $connection_type;

    #[ExcelProperty(value: '授权码', index: 18)]
    public string $auth_code;

    #[ExcelProperty(value: '贡献者ID', index: 19)]
    public string $sponsor_id;

    #[ExcelProperty(value: '贡献者', index: 20)]
    public string $sponsor_name;

    #[ExcelProperty(value: '贡献者链接', index: 21)]
    public string $sponsor_url;

    #[ExcelProperty(value: '共享状态', index: 22)]
    public string $sponsor_status;

    #[ExcelProperty(value: '扩展信息', index: 23)]
    public string $ext;

    #[ExcelProperty(value: 'creator', index: 24)]
    public string $created_by;

    #[ExcelProperty(value: 'updater', index: 25)]
    public string $updated_by;

    #[ExcelProperty(value: 'Creation time', index: 26)]
    public string $created_at;

    #[ExcelProperty(value: 'Update time', index: 27)]
    public string $updated_at;

    #[ExcelProperty(value: 'Delete time', index: 28)]
    public string $deleted_at;

    #[ExcelProperty(value: 'comment', index: 29)]
    public string $remark;
}

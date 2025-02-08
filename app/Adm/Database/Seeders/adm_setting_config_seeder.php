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
use App\Setting\Model\SettingConfig;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class AdmSettingConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SettingConfig::truncate();
        $data = $this->data();
        Db::table('setting_config')->insert(
            $data
        );
        Db::table('setting_config')->where('key', 'websocket_url')->update([
            'remark' => 'extra_websocket_url',
        ]);
        Db::table('setting_config')->where('key', 'enable_ping_china_map')->update([
            'remark' => 'extra_enable_ping_china_map',
        ]);
        Db::table('setting_config')->where('key', 'enable_warp_node')->update([
            'remark' => 'extra_enable_warp_node',
        ]);
    }

    public function data(): array
    {
        $jsonData = json_encode([
            ['label' => 'Chinese', 'value' => 'zh-CN'],
            ['label' => 'English', 'value' => 'en'],
        ]);

        return [
            [
                'group_id' => 1,
                'key' => 'site_name',
                'value' => 'Admin IM',
                'name' => 'Site Name',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 99,
            ],
            [
                'group_id' => 1,
                'key' => 'site_subtitle',
                'value' => 'Online ping | TCP delay test | Website Speed Test | Server Latency Test',
                'name' => 'Subtitle',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 98,
            ],
            [
                'group_id' => 1,
                'key' => 'site_url',
                'value' => '',
                'name' => 'Site Url',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 97,
            ],
            [
                'group_id' => 1,
                'key' => 'websocket_url',
                'value' => '',
                'name' => 'WebSocket Url',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 96,
            ],
            [
                'group_id' => 1,
                'key' => 'site_logo',
                'value' => '',
                'name' => 'Logo',
                'input_type' => 'upload',
                'config_select_data' => '',
                'sort' => 95,
            ],
            [
                'group_id' => 1,
                'key' => 'site_copyright',
                'name' => 'Copyright',
                'value' => 'Copyright © 2024-2025. All rights reserved.',
                'input_type' => 'textarea',
                'config_select_data' => '',
                'sort' => 94,
            ],
            [
                'group_id' => 1,
                'key' => 'site_record_number',
                'value' => '',
                'name' => 'ICP NO.',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 93,
            ],
            [
                'group_id' => 1,
                'key' => 'site_language',
                'value' => 'zh-CN',
                'name' => 'Api Language',
                'input_type' => 'select',
                'config_select_data' => $jsonData,
                'sort' => 92,
            ],
            [
                'group_id' => 1,
                'key' => 'index_banner',
                'value' => '',
                'name' => 'Top Banner',
                'input_type' => 'textarea',
                'config_select_data' => '',
                'sort' => 91,
            ],
            [
                'group_id' => 1,
                'key' => 'enable_ping_china_map',
                'value' => 'true',
                'name' => 'China Map',
                'input_type' => 'switch',
                'config_select_data' => '',
                'sort' => 90,
            ],
            [
                'group_id' => 1,
                'key' => 'enable_warp_node',
                'value' => 'false',
                'name' => 'Warp Node',
                'input_type' => 'switch',
                'config_select_data' => '',
                'sort' => 70,
            ],
            [
                'group_id' => 2,
                'key' => 'upload_allow_file',
                'value' => 'txt,doc,docx,xls,xlsx,ppt,pptx,rar,zip,7z,gz,pdf,wps,md',
                'name' => 'File Type',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 0,
            ],
            [
                'group_id' => 2,
                'key' => 'upload_allow_image',
                'value' => 'jpg,jpeg,png,gif,svg,bmp',
                'name' => 'Image Type',
                'input_type' => 'input',
                'config_select_data' => '',
                'sort' => 0,
            ],
            [
                'group_id' => 2,
                'key' => 'upload_mode',
                'value' => '1',
                'name' => 'Upload Mode',
                'input_type' => 'select',
                'config_select_data' => '[{"label":"Local","value":"1"},{"label":"Aliyun OSS","value":"2"},{"label":"Qiniuyun","value":"3"},{"label":"Tencent COS","value":"4"}]',
                'sort' => 99,
            ],
        ];
    }
}

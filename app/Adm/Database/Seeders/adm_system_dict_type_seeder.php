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
use App\System\Model\SystemDictType;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class AdmSystemDictTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = $this->data();

        foreach ($data as $value) {
            SystemDictType::create($value);
        }
        Db::statement('ALTER TABLE ' . SystemDictType::getModel()->getTable() . ' AUTO_INCREMENT = 100000');
    }

    public function data(): array
    {
        return [
            ['name' => '国家/地区', 'code' => 'country_code', 'status' => 1, 'created_by' => 0, 'updated_by' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null, 'remark' => ''],
            ['name' => '大洲', 'code' => 'continent_code', 'status' => 1, 'created_by' => 0, 'updated_by' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null, 'remark' => ''],
            ['name' => '运营商', 'code' => 'isp', 'status' => 1, 'created_by' => 0, 'updated_by' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null, 'remark' => ''],
            ['name' => '区域', 'code' => 'region', 'status' => 1, 'created_by' => 0, 'updated_by' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null, 'remark' => ''],
            ['name' => '服务商', 'code' => 'provider', 'status' => 1, 'created_by' => 0, 'updated_by' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'deleted_at' => null, 'remark' => ''],
        ];
    }
}

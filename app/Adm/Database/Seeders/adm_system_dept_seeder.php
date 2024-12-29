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
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class AdmSystemDeptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Db::table('system_dept')->truncate();
        Db::table('system_dept')->insert(
            [
                'parent_id' => 0,
                'level' => '0',
                'name' => 'Default',
                'created_by' => env('SUPER_ADMIN', 1),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
    }
}

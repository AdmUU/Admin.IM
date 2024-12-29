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

class AdmSystemUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Db::table('system_user_role')->insert(
            [
                'user_id' => 2,
                'role_id' => 2,
            ]
        );
    }
}

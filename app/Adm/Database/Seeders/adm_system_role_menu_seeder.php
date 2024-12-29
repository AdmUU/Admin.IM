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

class AdmSystemRoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Db::table('system_role_menu')->truncate();
        Db::table('system_role_menu')->insert([
            ['role_id' => '2', 'menu_id' => '2700'],
            ['role_id' => '2', 'menu_id' => '2701'],
            ['role_id' => '2', 'menu_id' => '2702'],
            ['role_id' => '2', 'menu_id' => '2703'],
            ['role_id' => '2', 'menu_id' => '2704'],
            ['role_id' => '2', 'menu_id' => '2705'],
            ['role_id' => '2', 'menu_id' => '2706'],
            ['role_id' => '2', 'menu_id' => '2707'],
            ['role_id' => '2', 'menu_id' => '2708'],
            ['role_id' => '2', 'menu_id' => '2709'],
            ['role_id' => '2', 'menu_id' => '2710'],
            ['role_id' => '2', 'menu_id' => '4500'],
            ['role_id' => '2', 'menu_id' => '4502'],
            ['role_id' => '2', 'menu_id' => '4505'],
            ['role_id' => '2', 'menu_id' => '4507'],
            ['role_id' => '2', 'menu_id' => '10000'],
            ['role_id' => '2', 'menu_id' => '10001'],
            ['role_id' => '2', 'menu_id' => '10002'],
            ['role_id' => '2', 'menu_id' => '10003'],
            ['role_id' => '2', 'menu_id' => '10004'],
            ['role_id' => '2', 'menu_id' => '10005'],
            ['role_id' => '2', 'menu_id' => '10006'],
            ['role_id' => '2', 'menu_id' => '10007'],
            ['role_id' => '2', 'menu_id' => '10100'],
            ['role_id' => '2', 'menu_id' => '10101'],
            ['role_id' => '2', 'menu_id' => '10102'],
            ['role_id' => '2', 'menu_id' => '10103'],
            ['role_id' => '2', 'menu_id' => '10104'],
            ['role_id' => '2', 'menu_id' => '10105'],
            ['role_id' => '2', 'menu_id' => '10106'],
            ['role_id' => '2', 'menu_id' => '10107'],
            ['role_id' => '2', 'menu_id' => '10108'],
        ]);
    }
}

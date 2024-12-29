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
use App\System\Model\SystemRole;
use Hyperf\Database\Seeders\Seeder;

class AdmSystemRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SystemRole::truncate();
        $data = $this->data();
        foreach ($data as $value) {
            SystemRole::create($value);
        }
    }

    public function data(): array
    {
        return [
            [
                'name' => 'Developer',
                'code' => 'developer',
                'data_scope' => 0,
                'sort' => 0,
                'created_by' => env('SUPER_ADMIN', 0),
                'updated_by' => 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Administrator',
                'code' => 'administrator',
                'data_scope' => 1,
                'sort' => 1,
                'created_by' => env('SUPER_ADMIN', 0),
                'updated_by' => 1,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}

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
use App\System\Model\SystemUser;
use Hyperf\Database\Seeders\Seeder;
use Mine\Helper\Str;

class AdmSystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SystemUser::truncate();
        $data = $this->data();
        foreach ($data as $value) {
            SystemUser::create($value);
        }
    }

    public function data(): array
    {
        $adminPass = Str::random(16);
        putenv('DEFAULT_ADMIN_PASSWORD=' . $adminPass);

        return [
            [
                'id' => 2,
                'username' => 'admin',
                'password' => $adminPass,
                'user_type' => '100',
                'nickname' => 'Admin',
                'dashboard' => 'statistics',
                'backend_setting' => '{"mode":"light","tag":true,"menuCollapse":false,"menuWidth":230,"layout":"banner","skin":"mine","i18n":true,"language":"zh_CN","animation":"ma-slide-down","color":"#22AB41","ws":true}',
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}

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
use App\Adm\Model\AdmNodeGroup;
use Hyperf\Database\Seeders\Seeder;

class AdmNodeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = $this->data();
        foreach ($data as $value) {
            AdmNodeGroup::firstOrCreate(
                ['id' => $value['id']],
                $value
            );
        }
    }

    public function data(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Default',
                'name_en' => 'default',
                'weight' => 99,
                'client_id' => substr(uuid(), 0, 8),
                'client_secret' => substr(str_replace('-', '', uuid()), 0, 16),
            ],
        ];
    }
}

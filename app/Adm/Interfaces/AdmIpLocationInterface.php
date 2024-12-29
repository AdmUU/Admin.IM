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

namespace App\Adm\Interfaces;

interface AdmIpLocationInterface
{
    public function search(string $ip, string $format = 'array'): array|string;

    public function getName(string $ip, string $locale = 'zh-CN'): string;
}

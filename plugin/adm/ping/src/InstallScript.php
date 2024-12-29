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

namespace Plugin\Ping;

use App\Adm\Utils\Plugin;

class InstallScript
{
    private string $pluginPath;

    public function __invoke()
    {
        $this->pluginPath = dirname(dirname(__FILE__));
        Plugin::installWebAdmin($this->pluginPath);
        Plugin::installWebUser($this->pluginPath);
    }
}

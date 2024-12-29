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

namespace App\Adm\Utils;

use Mine\AppStore\Utils\FileSystemUtils;
use Symfony\Component\Finder\Finder;

/**
 * Plugin install utils.
 */
class Plugin
{
    /**
     * Install front-end files for management backend.
     *
     * @param string $pluginPath Plugin path
     */
    public static function installWebAdmin(string $pluginPath)
    {
        $frontDirectory = config('mine-extension.front_directory');
        $webDirectory = $pluginPath . DIRECTORY_SEPARATOR . 'web-admin';
        if (file_exists($webDirectory)) {
            $finder = Finder::create()
                ->files()
                ->ignoreDotFiles(false)
                ->in($webDirectory);
            foreach ($finder as $file) {
                $relativeFilePath = $file->getRelativePathname();
                FileSystemUtils::copy($webDirectory . DIRECTORY_SEPARATOR . $relativeFilePath, $frontDirectory . DIRECTORY_SEPARATOR . $relativeFilePath);
            }
        }
    }

    /**
     * Install front-end files for user frontend.
     *
     * @param string $pluginPath Plugin path
     */
    public static function installWebUser(string $pluginPath)
    {
        $frontDirectory = BASE_PATH . DIRECTORY_SEPARATOR . 'web-user';
        $webDirectory = $pluginPath . DIRECTORY_SEPARATOR . 'web-user';
        if (file_exists($webDirectory)) {
            $finder = Finder::create()
                ->files()
                ->in($webDirectory);
            foreach ($finder as $file) {
                $relativeFilePath = $file->getRelativePathname();
                FileSystemUtils::copy($webDirectory . DIRECTORY_SEPARATOR . $relativeFilePath, $frontDirectory . DIRECTORY_SEPARATOR . $relativeFilePath);
            }
        }
    }
}

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

namespace Plugin\Webspeed;

use Mine\AppStore\Utils\FileSystemUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class UninstallScript
{
    private string $pluginPath;

    public function __invoke()
    {
        $this->pluginPath = dirname(dirname(__FILE__));
        $frontDirectory = config('mine-extension.front_directory');
        if (file_exists($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin')) {
            $finder = Finder::create()
                ->files()
                ->in($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin');
            foreach ($finder as $file) {
                /**
                 * @var SplFileInfo $file
                 */
                $relativeFilePath = $file->getRelativePathname();
                if (strpos($relativeFilePath, 'plugin') === 0) {
                    if (is_file($frontDirectory . $relativeFilePath)) {
                        $res = unlink($frontDirectory . $relativeFilePath);
                    }
                } else {
                    FileSystemUtils::recovery($relativeFilePath, $frontDirectory);
                }
            }
        }
    }
}

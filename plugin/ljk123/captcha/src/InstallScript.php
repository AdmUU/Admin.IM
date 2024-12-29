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

namespace Plugin\Captcha;

use App\Adm\Utils\Plugin;

class InstallScript
{
    private string $pluginPath;

    public function __invoke()
    {
        $this->pluginPath = dirname(dirname(__FILE__));
        $this->checkMainJS();
        $this->checkPlugin();
        $this->checkI18n();
        Plugin::installWebAdmin($this->pluginPath);
        Plugin::installWebUser($this->pluginPath);
    }

    /**
     * 查找前端文件的main.js里是否包含.use(plugin)
     * 没有的话生成一个main.js覆盖过去.
     */
    private function checkMainJS()
    {
        $frontDirectory = config('mine-extension.front_directory') . DIRECTORY_SEPARATOR . 'src';
        if (! is_file($frontDirectory . DIRECTORY_SEPARATOR . 'main.js')) {
            $console = console();
            $console->warning('未找到main.js,请手动确认是否Vue.use(plugin)');
            return;
        }
        $mainJs = file_get_contents($frontDirectory . DIRECTORY_SEPARATOR . 'main.js');
        if (strpos($mainJs, '.use(plugin)') === false) {
            file_put_contents($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'main.js', $mainJs . "\n\n//plugin\nimport plugin from './plugin'\napp.use(plugin)");
        }
    }

    /**
     * 查找前端文件的是否包含 plugin/index.js.
     */
    private function checkPlugin()
    {
        $frontDirectory = config('mine-extension.front_directory') . DIRECTORY_SEPARATOR . 'src';
        if (! is_file($frontDirectory . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . 'index.js')) {
            file_put_contents($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . 'index.js', self::pluginjs());
        }
    }

    private function checkI18n()
    {
        $frontDirectory = config('mine-extension.front_directory') . DIRECTORY_SEPARATOR . 'src';
        if (! is_file($frontDirectory . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'zh_CN' . DIRECTORY_SEPARATOR . 'plugin.js')) {
            mkdir($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'zh_CN', 0777, true);
            file_put_contents($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'zh_CN' . DIRECTORY_SEPARATOR . 'plugin.js', self::i18njs());
        }
        if (! is_file($frontDirectory . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'plugin.js')) {
            mkdir($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'en', 0777, true);
            file_put_contents($this->pluginPath . DIRECTORY_SEPARATOR . 'web-admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'plugin.js', self::i18njs());
        }
    }

    private static function pluginjs()
    {
        return <<<'JS'
export default {
    install:(Vue)=>{
       const mains= import.meta.glob('./*/main.js')
       for (const path in mains) {
        mains[path]().then((module) => {
            Vue.use(module.default)
        });
      }
    }
}
JS;
    }

    private static function i18njs()
    {
        return <<<'JS'
export default {
};

JS;
    }
}

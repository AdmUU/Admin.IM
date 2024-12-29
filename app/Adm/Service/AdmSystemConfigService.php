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

namespace App\Adm\Service;

use App\System\Service\SystemUploadFileService;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Mine\Abstracts\AbstractService;

/**
 * System Config Service.
 */
class AdmSystemConfigService extends AbstractService
{
    #[Inject]
    protected SystemUploadFileService $uploadFileService;

    #[Cacheable(
        prefix: 'adm:api:config',
        ttl: 600,
        listener: 'site-config-update'
    )]
    /**
     * Get site configuration.
     */
    public function getSiteConfig(): array
    {
        $site_name = sys_config('site_name');
        $site_subtitle = sys_config('site_subtitle');
        $site_copyright = sys_config('site_copyright');
        $site_record_number = sys_config('site_record_number');
        $index_banner = sys_config('index_banner');
        $site_url_config = sys_config('site_url');
        $site_url = $site_url_config['value'] ?? '';
        $site_logo = $site_url . '/static/assets/images/logo.svg';
        $logo_hash = sys_config('site_logo');
        if ($logo_hash['value'] !== null) {
            $log_url = $this->uploadFileService->readByHash($logo_hash['value']);
            if (isset($log_url['url'])) {
                $site_logo = $site_url . $log_url['url'];
            }
        }
        return [
            'site_name' => $site_name['value'],
            'site_subtitle' => $site_subtitle['value'],
            'site_logo' => $site_logo,
            'site_copyright' => $site_copyright['value'],
            'site_record_number' => $site_record_number['value'],
            'index_banner' => $index_banner['value'],
        ];
    }
}

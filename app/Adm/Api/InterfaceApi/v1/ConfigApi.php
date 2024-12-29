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

namespace App\Adm\Api\InterfaceApi\v1;

use App\Adm\Annotation\ApiLimit;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmSystemConfigService;
use Hyperf\Di\Annotation\Inject;
use Mine\Annotation\Api\MApi;
use Mine\MineResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * System config api.
 */
class ConfigApi
{
    #[Inject]
    protected AdmSystemConfigService $service;

    #[Inject]
    protected MineResponse $response;

    /**
     * Get site config information.
     */
    #[ApiLimit]
    #[MApi(accessName: 'getSiteConfig', name: 'Get site config', description: 'Get site config', appId: '0', authMode: AdmAuthService::AUTH_MODE_NONE)]
    public function getSiteConfig(): ResponseInterface
    {
        return $this->response->success('Success', $this->service->getSiteConfig());
    }
}

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
use App\System\Service\SystemNoticeService;
use Hyperf\Di\Annotation\Inject;
use Mine\Annotation\Api\MApi;
use Mine\MineResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Get system notice.
 */
class NoticeApi
{
    #[Inject]
    protected SystemNoticeService $service;

    #[Inject]
    protected MineResponse $response;

    /**
     * Get system notice list.
     */
    #[ApiLimit]
    #[MApi(accessName: 'getNotice', name: 'Get system notice list', description: 'Get system notice list', appId: '0', authMode: AdmAuthService::AUTH_MODE_NONE)]
    public function getNotice(): ResponseInterface
    {
        $list = $this->service->getPageList(['select' => 'id, title, content'], false);
        return $this->response->success('Success', $list['items']);
    }
}

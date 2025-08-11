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

namespace Plugin\Webspeed\Controller;

use App\Adm\Annotation\ApiLimit;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmNodeService;
use App\Adm\Service\AdmSystemDictDataService;
use App\Adm\Utils\NetworkUtils;
use App\System\Mapper\SystemDeptMapper;
use App\System\Mapper\SystemUserMapper;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Mine\MineResponse;
use Plugin\Webspeed\Request\WebspeedRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Webspeed plugin.
 */
#[Controller(prefix: 'plugin/webspeed')]
class IndexController
{
    protected SystemUserMapper $user;

    protected SystemDeptMapper $dept;

    protected MineResponse $response;

    #[Inject]
    protected AdmNodeService $nodeService;

    #[Inject]
    protected AdmSystemDictDataService $dictDataService;

    #[Inject]
    protected AdmAuthService $authService;

    /**
     * Api constructor.
     */
    public function __construct(SystemUserMapper $user, SystemDeptMapper $dept)
    {
        $this->response = container()->get(MineResponse::class);
        $this->user = $user;
        $this->dept = $dept;
    }

    /**
     * Get request token interface.
     */
    #[ApiLimit]
    #[PostMapping('requestTask')]
    public function requestTask(WebspeedRequest $WebspeedRequest): ResponseInterface
    {
        $data = $WebspeedRequest->validated();
        if (! $validateHost = NetworkUtils::validateIPDomain($data['content'])) {
            return $this->response->error(t('adm.invalid host'));
        }
        if ($validateHost['address_type'] == 'domain') {
            if (! $prefer_ip_type = NetworkUtils::preferIPType($validateHost['address'])) {
                return $this->response->error(t('adm.invalid host'));
            }
        } else {
            $prefer_ip_type = $validateHost['address_type'];
        }
        // $validateHost['address'] = $validateHost['address_type'] == 'ipv4' ? $validateHost['address'] : '[' . $validateHost['address'] . ']';
        $locale = $data['locale'] == 'en' || $data['locale'] == 'en-US' ? 'en' : 'zh-CN';
        $configAddr = sys_config('site_url');
        $token = $this->authService->getSocketToken('web');
        $taskId = $this->authService->setSocketTask(
            'webspeed',
            [
                'address_type' => $validateHost['address_type'],
                'client_type' => $data['client_type'] ?? 'web',
                'client_ip' => NetworkUtils::getClientIp(),
                'type' => $data['type'],
                'content' => $data['content'],
                'prefer_ip_type' => $prefer_ip_type,
                'locale' => $locale,
                'loc' => 'all',
                'class_name' => 'Plugin\Webspeed\Service\WebspeedService',
                'request_method' => 'processRequestTask',
                'response_method' => 'processResponseTask',
            ]
        );
        $res = [
            'websocket_address' => $configAddr['value'] . '/web',
            'node_type' => $validateHost['address_type'],
            'content' => $data['content'],
            'token' => $token,
            'task_id' => $taskId,
        ];
        return $this->response->success('Success', $res);
    }
}

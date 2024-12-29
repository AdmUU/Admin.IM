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

namespace Plugin\Ping\Controller;

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
use Plugin\Ping\Request\PingRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Ping plugin.
 */
#[Controller(prefix: 'plugin/ping')]
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
    public function requestTask(PingRequest $PingRequest): ResponseInterface
    {
        $data = $PingRequest->validated();
        if (! $validateHost = NetworkUtils::validateIPDomain($data['host'])) {
            return $this->response->error(t('adm.invalid host'));
        }
        if ($validateHost['address_type'] == 'domain') {
            if (! $prefer_ip_type = NetworkUtils::preferIPType($validateHost['address'])) {
                return $this->response->error(t('adm.invalid host'));
            }
        } else {
            $prefer_ip_type = $validateHost['address_type'];
        }
        $validateHost['address'] = $validateHost['address_type'] == 'ipv4' ? $validateHost['address'] : '[' . $validateHost['address'] . ']';
        if ($data['ping_protocol'] == 'tcp') {
            $hostPort = $validateHost['port'] ?? (strpos(trim($data['host']), 'https://') === 0 ? 443 : 80);
            $validateHost['address'] = $validateHost['address'] . ':' . $hostPort;
        }
        $locale = $data['locale'] == 'en' || $data['locale'] == 'en-US' ? 'en' : 'zh-CN';
        $configAddr = sys_config('site_url');
        $token = $this->authService->getSocketToken('web');
        $taskId = $this->authService->setSocketTask(
            'ping',
            [
                'address_type' => $validateHost['address_type'],
                'client_type' => $data['client_type'] ?? 'web',
                'ping_protocol' => $data['ping_protocol'],
                'ping_type' => $data['ping_type'],
                'host' => $validateHost['address'],
                'prefer_ip_type' => $prefer_ip_type,
                'locale' => $locale,
                'loc' => 'all',
                'class_name' => 'Plugin\Ping\Service\PingService',
                'request_method' => 'processRequestTask',
                'response_method' => 'processResponseTask',
            ]
        );
        $res = [
            'websocket_address' => $configAddr['value'] . '/web',
            'node_type' => $validateHost['address_type'],
            'host' => $validateHost['address'],
            'token' => $token,
            'task_id' => $taskId,
        ];
        return $this->response->success('Success', $res);
    }
}

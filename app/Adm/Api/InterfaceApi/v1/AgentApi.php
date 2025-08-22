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
use App\Adm\Api\Request\v1\AdmApiRequest;
use App\Adm\Service\AdmAuthService;
use App\Adm\Service\AdmNodeService;
use App\Adm\Utils\AdmCode;
use Hyperf\Di\Annotation\Inject;
use Mine\Annotation\Api\MApi;
use Mine\Exception\NormalStatusException;
use Mine\MineResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Agent service api.
 */
class AgentApi
{
    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    protected AdmNodeService $nodeService;

    #[Inject]
    protected MineResponse $response;

    /**
     * Request a token for agent.
     */
    #[ApiLimit]
    #[MApi(accessName: 'requestAgentToken', name: 'Request Socket Agent Token', description: 'Request a token for socket agent', appId: '0', authMode: AdmAuthService::AUTH_MODE_NONE)]
    public function requestAgentToken(AdmApiRequest $AdmApiRequest): ResponseInterface
    {
        $data = $AdmApiRequest->validated();

        $this->authService->checkAgentIP('');

        if (! $node = $this->authService->checkAuthCode($data['auth_code'], true)) {
            throw new NormalStatusException(t(key: 'adm.auth_code_verification_fail'), AdmCode::API_AUTH_CODE_NOT_FOUND);
        }
        if ($node['block'] == 1) {
            throw new NormalStatusException(t(key: 'adm.auth_code_verification_fail'), AdmCode::API_AUTH_CODE_BLOCKED);
        }
        if ($node['enable'] == 0) {
            throw new NormalStatusException(t(key: 'adm.auth_code_verification_fail'), AdmCode::API_AUTH_CODE_DISABLED);
        }
        $redis = redis();
        if ($redis->hget('node:connect', (string) $node['id'])) {
            throw new NormalStatusException(t(key: 'adm.socket_already_connected') . $node['id'], AdmCode::SOCKET_ALREADY_CONNECTED);
        }
        $this->nodeService->updateAgent($node, $data);
        return $this->response->success('Success', [
            'token' => $this->authService->getSocketToken('agent'),
        ]);
    }
}

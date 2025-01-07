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
use App\Adm\Service\AdmNodeGroupService;
use App\Adm\Service\AdmNodeService;
use App\Adm\Service\AdmSystemDictDataService;
use Hyperf\Di\Annotation\Inject;
use Mine\Annotation\Api\MApi;
use Mine\MineResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Node service api.
 */
class NodeApi
{
    #[Inject]
    protected AdmNodeService $service;

    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    protected AdmNodeGroupService $nodeGroupService;

    #[Inject]
    protected AdmSystemDictDataService $dictDataService;

    #[Inject]
    protected MineResponse $response;

    /**
     * Get node list.
     */
    #[ApiLimit]
    #[MApi(accessName: 'getNodeList', name: 'Get node list', description: 'Get node list', appId: '0', authMode: AdmAuthService::AUTH_MODE_NONE)]
    public function getNodeList(AdmApiRequest $AdmApiRequest): ResponseInterface
    {
        $data = $AdmApiRequest->validated();
        $data['prefer_ip_type'] = $data['prefer_ip_type'] ?? 'dual';
        $data['dict_code'] = $data['dict_code'] ?? 'all';
        $data['dict_value'] = $data['dict_value'] ?? 'all';
        $data['task_type'] = $data['task_type'] ?? '';
        $data['task_id'] = $data['task_id'] ?? '';
        return $this->response->success('Success', $this->service->snapshotDictNode($data));
    }

    /**
     * Register a new node.
     */
    #[ApiLimit]
    #[MApi(accessName: 'registNode', name: 'Register Node', description: 'Register a new node', appId: '0', authMode: AdmAuthService::AUTH_MODE_ENCRYPT_PARAM)]
    public function registNode(AdmApiRequest $AdmApiRequest): ResponseInterface
    {
        $params = $AdmApiRequest->validated();
        $params = $this->authService->getRegistIP($params);
        $params['auth_code'] ??= '';
        if (! empty($params['auth_code'])) {
            if ($node = $this->authService->checkAuthCode($params['auth_code'], true)) {
                $this->service->updateAgent($node, $params);
                $res = ['auth_code' => $params['auth_code'], 'did' => (string) $node['did']];
                return $this->response->success('Success', $res);
            }
        }
        return $this->response->success('Success', $this->service->save($params));
    }
}

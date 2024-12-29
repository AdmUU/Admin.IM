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

namespace App\Adm\Api\Middleware;

use App\Adm\Api\Event\ApiFinish;
use App\Adm\Service\AdmAuthService;
use App\System\Model\SystemApi;
use App\System\Service\SystemApiService;
use App\System\Service\SystemAppService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Mine\Annotation\Api\MApi;
use Mine\Annotation\Api\MApiCollector;
use Mine\Exception\NormalStatusException;
use Mine\Helper\MineCode;
use Mine\MineRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Api verify middleware.
 */
class VerifyInterfaceMiddleware implements MiddlewareInterface
{
    /**
     * Event Scheduler.
     */
    #[Inject]
    protected EventDispatcherInterface $evDispatcher;

    /**
     * Verify check interface.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->run($request, $handler);
    }

    /**
     * Access interface authentication processing.
     *
     * @throws RuntimeException
     * @throws NormalStatusException
     */
    protected function auth(ServerRequestInterface $request): int
    {
        try {
            /**
             * @var SystemAppService $service
             */
            $service = container()->get(SystemAppService::class);
            $auth = container()->get(AdmAuthService::class);
            $queryParams = $request->getQueryParams();
            $apiData = $this->_getApiData();
            switch ($apiData['auth_mode']) {
                case MApi::AUTH_MODE_EASY:
                    if (empty($queryParams['app_id'])) {
                        return MineCode::API_APP_ID_MISSING;
                    }
                    if (empty($queryParams['identity'])) {
                        return MineCode::API_IDENTITY_MISSING;
                    }
                    return $service->verifyEasyMode($queryParams['app_id'], $queryParams['identity'], $apiData);
                case MApi::AUTH_MODE_NORMAL:
                    if (empty($queryParams['access_token'])) {
                        return MineCode::API_ACCESS_TOKEN_MISSING;
                    }
                    return $service->verifyNormalMode($queryParams['access_token'], $apiData);
                case AdmAuthService::AUTH_MODE_ENCRYPT_PARAM:
                    $postParams = $request->getParsedBody();
                    return $auth->encParamVerify(array_merge($postParams, $queryParams));
                case AdmAuthService::AUTH_MODE_NONE:
                    return MineCode::API_VERIFY_PASS;
                default:
                    throw new \RuntimeException();
            }
        } catch (\Throwable $e) {
            \exception_log($e);
            throw new NormalStatusException($e->getMessage(), MineCode::API_AUTH_EXCEPTION);
        }
    }

    /**
     * API general check.
     *
     * @param mixed $request
     * @throws NormalStatusException
     */
    protected function apiModelCheck($request): ServerRequestInterface
    {
        $apiData = MApiCollector::getApiInfos();

        $mineRequest = container()->get(MineRequest::class);

        if (isset($apiData[$mineRequest->route('method')])) {
            $apiModel = $apiData[$mineRequest->route('method')];

            if ($apiModel['status'] == SystemApi::DISABLE) {
                throw new NormalStatusException(t('mineadmin.api_stop'), MineCode::RESOURCE_STOP);
            }

            if ($apiModel['request_mode'] !== MApi::METHOD_ALL && $request->getMethod()[0] !== $apiModel['request_mode']) {
                throw new NormalStatusException(
                    t('mineadmin.not_allow_method', ['method' => $request->getMethod()]),
                    MineCode::METHOD_NOT_ALLOW
                );
            }

            $this->_setApiData($apiModel);

            return $request->withParsedBody(array_merge(
                $request->getParsedBody(),
                ['apiData' => $apiModel]
            ));
        }

        $service = container()->get(SystemApiService::class);
        $apiModel = $service->mapper->one(function ($query) {
            $request = container()->get(MineRequest::class);
            $query->where('access_name', $request->route('method'));
        });

        if (! $apiModel) {
            throw new NormalStatusException(t('mineadmin.not_found'), MineCode::NOT_FOUND);
        }

        if ($apiModel['status'] == SystemApi::DISABLE) {
            throw new NormalStatusException(t('mineadmin.api_stop'), MineCode::RESOURCE_STOP);
        }

        if ($apiModel['request_mode'] !== MApi::METHOD_ALL && $request->getMethod()[0] !== $apiModel['request_mode']) {
            throw new NormalStatusException(
                t('mineadmin.not_allow_method', ['method' => $request->getMethod()]),
                MineCode::METHOD_NOT_ALLOW
            );
        }

        $this->_setApiData($apiModel->toArray());

        return $request->withParsedBody(array_merge(
            $request->getParsedBody(),
            ['apiData' => $apiModel->toArray()]
        ));
    }

    /**
     * Run.
     *
     * @throws NormalStatusException
     */
    protected function run(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->apiModelCheck($request);

        if (($code = $this->auth($request)) !== MineCode::API_VERIFY_PASS) {
            throw new NormalStatusException(t('mineadmin.api_auth_fail'), $code);
        }

        $result = $handler->handle($request);

        $event = new ApiFinish($this->_getApiData(), $result);
        $this->evDispatcher->dispatch($event);

        return $event->getResult();
    }

    /**
     * Set up the coroutine context.
     */
    private function _setApiData(array $data): void
    {
        Context::set('apiData', $data);
    }

    /**
     * Get the coroutine context.
     */
    private function _getApiData(): mixed
    {
        return Context::get('apiData', []);
    }
}

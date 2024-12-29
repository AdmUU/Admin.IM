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

use App\Adm\Service\AdmAuthService;
use App\Adm\Utils\AdmCode;
use App\Adm\Utils\NetworkUtils;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as Rpsi;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Hyperf\WebSocketServer\Context;
use Mine\Exception\NormalStatusException;
use Mine\MineRequest;
use Mine\Redis\MineLockRedis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Support\make;

/**
 * SocketIO Auth Middleware.
 */
class SocketAuthMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected Rpsi $response;

    #[Inject]
    protected AdmAuthService $service;

    public function __construct(ContainerInterface $container, Rpsi $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    /**
     * Process.
     */
    public function process(ServerRequestInterface $serverRequest, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = container()->get(MineRequest::class);
        $socketToken = $request->input('token', null);
        $authCode = $request->input('auth_code', null);
        $locale = $request->input('locale', 'zh-CN');
        try {
            if (! $ip = NetworkUtils::getClientIp(null, true)) {
                throw new NormalStatusException(t('adm.ip_verification_fail'), code: AdmCode::API_AUTH_IP_FAILD);
            }

            $ipKey = substr(md5($ip), 0, 16);
            $request60_key = 'apiLimit:ip:' . $ipKey . ':60';
            $ip_lock_key = 'apiLimit:ip:lock:' . $ipKey;

            $lockRedis = new MineLockRedis(
                make(Redis::class),
                make(LoggerFactory::class)->get('Mine Redis Lock')
            );
            $lockRedis->setTypeName('apiLimit');
            $redis = redis();
            if ($lockRedis->check($ip_lock_key)) {
                $lockRedis = null;
                throw new NormalStatusException('Socket' . t('adm.too_many_request'), AdmCode::TOO_MANY_REQUEST);
            }
            if (! $redis->exists($request60_key)) {
                $redis->set($request60_key, 0, ['nx', 'ex' => 60]);
            }
            $request60_num = $redis->incr($request60_key);
            if ($request60_num > 30) {
                $lockRedis->lock($ip_lock_key, 120);
                $lockRedis = null;
                throw new NormalStatusException('Socket' . t('adm.too_many_request'), AdmCode::TOO_MANY_REQUEST);
            }
            $lockRedis = null;

            if ($socketToken && $type = $this->service->checkSocketToken($socketToken)) {
                Context::set('socket:type', $type);
                $locale = $locale == 'en' ? 'en' : 'zh-CN';
                Context::set('socket:locale', $locale);
                if ($type == 'web') {
                    return $handler->handle($serverRequest);
                }
                if ($type == 'agent' && $authCode && $node = $this->service->checkAuthCode($authCode)) {
                    if (! $redis->hSetNx('node:connect', (string) $node['id'], date('Y-m-d H:i:s'))) {
                        throw new NormalStatusException('Socket' . t('adm.socket_already_connected') . $node['id'], AdmCode::SOCKET_ALREADY_CONNECTED);
                    }
                    $redis->hset('node:alive', (string) $node['id'], time());
                    Context::set('socket:nodeID', $node['id']);
                    Context::set('socket:nodeDid', $node['did']);
                    return $handler->handle($serverRequest);
                }
            }
            return $this->container->get(Rpsi::class)->raw('Forbidden');
        } catch (\Exception $e) {
            exception_log($e);
            return $this->container->get(Rpsi::class)->raw('Forbidden');
        }
    }
}

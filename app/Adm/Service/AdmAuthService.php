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

use App\Adm\Mapper\AdmNodeMapper;
use App\Adm\Utils\AdmCode;
use App\Adm\Utils\NetworkUtils;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Mine\Abstracts\AbstractService;
use Mine\Exception\NormalStatusException;
use Mine\Helper\MineCode;
use Mine\Redis\MineLockRedis;
use Psr\SimpleCache\CacheInterface;

/**
 * Auth Service.
 */
class AdmAuthService extends AbstractService
{
    public const AUTH_MODE_NONE = 101;

    public const AUTH_MODE_ENCRYPT_PARAM = 102;

    #[Inject]
    private CacheInterface $cache;

    #[Inject]
    private AdmNodeMapper $nodeMapper;

    private string $cachePrefix;

    public function __construct()
    {
        $this->cachePrefix = 'adm_auth';
    }

    /**
     * Get socket token.
     */
    public function getSocketToken(string $type = 'web'): string
    {
        $token = Str::random(40);
        $this->cache->set($this->cachePrefix . ':st:token:' . $token, $type, 60);
        return $token;
    }

    /**
     * Check socket token.
     *
     * @param mixed $token
     */
    public function checkSocketToken($token): ?string
    {
        return $this->cache->get($this->cachePrefix . ':st:token:' . $token);
    }

    /**
     * Set socket task.
     *
     * @param mixed $type
     * @param mixed $data
     * @param mixed $taskId
     */
    public function setSocketTask($type, $data, $taskId = null): string
    {
        $taskId ??= uniqid();
        $this->cache->set($this->cachePrefix . ':st:task:' . $type . ':' . $taskId, $data, 300);
        return $taskId;
    }

    /**
     * Get socket task.
     *
     * @param mixed $type
     * @param mixed $taskId
     */
    public function getSocketTask(string $type, string $taskId): ?array
    {
        return $this->cache->get($this->cachePrefix . ':st:task:' . $type . ':' . $taskId);
    }

    /**
     * Check authnode.
     */
    #[Cacheable(prefix: 'adm:authcode', value: '_#{authCode}_#{ignoreBlock}', ttl: 600, listener: 'node-enable')]
    public function checkAuthCode(string $authCode, bool $ignoreBlock = false): ?array
    {
        return $this->nodeMapper->getNodeByAuthCode($authCode, $ignoreBlock);
    }

    /**
     * Check fingerprint.
     */
    public function checkFingerprint(string $fingerprint): array|bool
    {
        return $this->nodeMapper->getNodeByFingerprint($fingerprint);
    }

    /**
     * Parameter encryption verification.
     */
    public function encParamVerify(array $params): int
    {
        $validationFactory = container()->get(ValidatorFactoryInterface::class);
        $validator = $validationFactory->make(
            $params,
            [
                'timestamp' => 'digits:10|required',
                'nonce' => 'string|size:32|required',
                'signature' => 'string|size:64|required',
            ]
        );

        if ($validator->fails()) {
            return MineCode::API_PARAMS_ERROR;
        }

        $signature = $params['signature'];
        $clientKey = $params['key'];
        $timestamp = $params['timestamp'];
        $nonce = $params['nonce'];
        unset($params['apiData'], $params['signature']);

        ksort($params);

        if (abs($timestamp - time()) > 300) {
            return AdmCode::API_AUTH_TIMESTAMP_WRONG;
        }

        $nodeGroupService = container()->get(AdmNodeGroupService::class);
        if (! $nodegroup = $nodeGroupService->getGroupByClientID($clientKey)) {
            throw new NormalStatusException(t(key: 'adm.client_id_verification_fail'), AdmCode::API_AUTH_CLIENT_ID_FAILD);
        }

        $clientSecret = $nodegroup['client_secret'];
        $queryString = http_build_query($params);
        $computedSignature = hash_hmac('sha256', $queryString, $clientSecret);

        if ($signature !== $computedSignature) {
            return MineCode::API_SIGN_ERROR;
        }
        $nonce_key = 'adm:nonce:' . $nonce;

        $lockRedis = new MineLockRedis(
            make(Redis::class),
            make(LoggerFactory::class)->get('Mine Redis Lock')
        );
        $lockRedis->setTypeName('admNonce');
        if ($lockRedis->check($nonce_key)) {
            $lockRedis = null;
            return AdmCode::API_AUTH_NONCE_DUPLICATE;
        }
        $lockRedis->lock($nonce_key, 300);

        return MineCode::API_VERIFY_PASS;
    }

    /**
     * Agent regist IP params.
     */
    public function getRegistIP(array $params): array
    {
        $params['ip'] = NetworkUtils::getClientIp();
        if (! config('app_debug')) {
            if (filter_var($params['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $params['ipv4'] = $params['ip'];
            } elseif (filter_var($params['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $params['ipv6'] = $params['ip'];
            } else {
                throw new NormalStatusException(t(key: 'adm.ip_verification_fail'), AdmCode::API_AUTH_IP_FAILD);
            }
        }
        if (! isset($params['ipv4']) || $params['ipv4'] == '') {
            $params['ipv4'] = null;
        }
        if (! isset($params['ipv6']) || $params['ipv6'] == '') {
            $params['ipv6'] = null;
        }
        if (empty($params['ipv4']) && empty($params['ipv6'])) {
            throw new NormalStatusException(t(key: 'adm.ip_verification_fail'), AdmCode::API_AUTH_IP_FAILD);
        }
        $this->checkAgentIP($params['ipv4'], $params['ipv6']);

        return $params;
    }

    /**
     * Agent IP verification.
     */
    public function checkAgentIP(?string $ip = null, ?string $ipv6 = null)
    {
        if (empty($ip) && empty($ipv6)) {
            $ip = NetworkUtils::getClientIp();
        }

        $enable_warp_node = sys_config('enable_warp_node')['value'] ?? 'false';
        if ($enable_warp_node == 'false' && (NetworkUtils::isCFIp($ip) || NetworkUtils::isCFIp($ipv6))) {
            throw new NormalStatusException(t(key: 'adm.ip_verification_cf_fail'), AdmCode::API_AUTH_IP_FAILD);
        }
    }
}

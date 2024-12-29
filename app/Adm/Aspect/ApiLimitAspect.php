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

namespace App\Adm\Aspect;

use App\Adm\Annotation\ApiLimit;
use App\Adm\Utils\AdmCode;
use App\Adm\Utils\NetworkUtils;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Mine\Exception\NormalStatusException;
use Mine\Helper\MineCode;
use Mine\MineRequest;
use Mine\Redis\MineLockRedis;
use Plugin\Captcha\Service\Service as Captcha;

use function Hyperf\Support\make;

/**
 * Aspect for ApiLimit.
 */
#[Aspect]
class ApiLimitAspect extends AbstractAspect
{
    public array $annotations = [
        ApiLimit::class,
    ];

    #[Inject]
    private Captcha $captchaService;

    /**
     * process.
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        try {
            if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[ApiLimit::class]) && env('ADM_API_LIMIT', true)) {
                $apiLimit = $proceedingJoinPoint->getAnnotationMetadata()->method[ApiLimit::class];
            } else {
                return $proceedingJoinPoint->process();
            }

            $request = container()->get(MineRequest::class);

            if (! $ip = NetworkUtils::getClientIp($request, true)) {
                throw new NormalStatusException('Unable to verify client ip', code: MineCode::INTERFACE_EXCEPTION);
            }

            $key = substr(md5(sprintf('%s-%s-%s', $ip, $request->getPathInfo(), $request->getMethod())), 0, 16);
            $ipKey = substr(md5($ip), 0, 16);
            $request10_key = 'apiLimit:' . $key . ':10';
            $request60_key = 'apiLimit:ip:' . $ipKey . ':60';
            $ip_lock_key = 'apiLimit:ip:lock:' . $ipKey;
            $ip_free_key = 'apiLimit:ip:free:' . $ipKey;
            Context::set('ipKey', $ipKey);

            $lockRedis = new MineLockRedis(
                make(Redis::class),
                make(LoggerFactory::class)->get('Mine Redis Lock')
            );
            $lockRedis->setTypeName('apiLimit');

            if ($lockRedis->check($ip_free_key)) {
                $lockRedis = null;
                return $proceedingJoinPoint->process();
            }

            $redis = redis();
            if ($lockRedis->check($ip_lock_key)) {
                $captcha_type = (int) $request->input('captcha_type');
                $captcha_key = (string) $request->input('captcha_key');
                $captcha_id = (string) $request->input('captcha_id');
                if ($this->captchaService->verify($captcha_type, $captcha_id, $captcha_key)) {
                    $lockRedis->freed($ip_lock_key);
                    $lockRedis->lock($ip_free_key, 120);
                    $redis->del($request10_key);
                    $redis->del($request60_key);
                    $lockRedis = null;
                    return $proceedingJoinPoint->process();
                }
                $lockRedis = null;

                throw new NormalStatusException($apiLimit->message ?: t('adm.please_complete_captcha_verification'), AdmCode::API_NEED_CAPTCHA);
            }
            if (! $redis->exists($request10_key)) {
                $redis->set($request10_key, 0, ['nx', 'ex' => 10]);
            }
            $request10_num = $redis->incr($request10_key);
            if ($request10_num > $apiLimit->num10) {
                $lockRedis->lock($ip_lock_key, 60);
                $lockRedis = null;
                throw new NormalStatusException($apiLimit->message ?: t('adm.need_captcha_verification_for_10s'), AdmCode::API_NEED_CAPTCHA);
            }

            if (! $redis->exists($request60_key)) {
                $redis->set($request60_key, 0, ['nx', 'ex' => 60]);
            }
            $request60_num = $redis->incr($request60_key);
            if ($request60_num > $apiLimit->num60) {
                $lockRedis->lock($ip_lock_key, 60);
                $lockRedis = null;
                throw new NormalStatusException($apiLimit->message ?: t('adm.need_captcha_verification'), AdmCode::API_NEED_CAPTCHA);
            }
            $lockRedis = null;
            return $proceedingJoinPoint->process();
        } catch (\Exception $e) {
            exception_log($e);
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }
}

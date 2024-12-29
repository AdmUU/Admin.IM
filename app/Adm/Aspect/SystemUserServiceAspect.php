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

use App\Adm\Model\AdmNode;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Mine\Exception\NormalStatusException;

/**
 * Aspect for SystemUserService.
 */
#[Aspect]
class SystemUserServiceAspect extends AbstractAspect
{
    public array $classes = [
        'App\System\Service\SystemUserService',
    ];

    public array $annotations = [
    ];

    /**
     * @var AdmNode
     */
    public $model;

    /**
     * process.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        try {
            $this->model = AdmNode::class;

            $methodName = $proceedingJoinPoint->methodName;
            if ($methodName === 'clearCache') {
                return $this->clearCache($proceedingJoinPoint);
            }

            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            logger('SystemUserServiceAspect')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * clearCache.
     */
    public function clearCache(ProceedingJoinPoint $proceedingJoinPoint): bool
    {
        $arguments = $proceedingJoinPoint->arguments;
        $id = $arguments['keys']['id'];

        $redis = redis();
        $prefix = config('cache.default.prefix');

        $iterator = null;
        while (false !== ($configKey = $redis->scan($iterator, $prefix . 'config:*', 100))) {
            $redis->del($configKey);
        }

        $iterator = null;
        while (false !== ($dictKey = $redis->scan($iterator, $prefix . 'system:dict:*', 100))) {
            $redis->del($dictKey);
        }

        $iterator = null;
        while (false !== ($systemConfigKey = $redis->scan($iterator, $prefix . 'system:config:*', 100))) {
            $redis->del($systemConfigKey);
        }

        $iterator = null;
        while (false !== ($admKey = $redis->scan($iterator, $prefix . 'adm:*', 100))) {
            $redis->del($admKey);
        }

        $redis->del([$prefix . 'crontab', $prefix . 'modules']);

        return $redis->del("{$prefix}loginInfo:userId_{$id}") > 0;
    }
}

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

use App\Setting\Mapper\SettingConfigMapper;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Context\ApplicationContext;
use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Mine\Exception\NormalStatusException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Aspect for SettingConfigService.
 */
#[Aspect]
class SettingConfigServiceAspect extends AbstractAspect
{
    public array $classes = [
        'App\Setting\Service\SettingConfigService',
    ];

    public array $annotations = [
    ];

    #[Inject]
    private SettingConfigMapper $mapper;

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        try {
            $methodName = $proceedingJoinPoint->methodName;
            if ($methodName === 'updatedByKeys') {
                return $this->updatedByKeys($proceedingJoinPoint);
            }

            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            logger('SystemUserServiceAspect')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * updatedByKeys.
     */
    #[Transactional]
    public function updatedByKeys(ProceedingJoinPoint $proceedingJoinPoint): bool
    {
        $arguments = $proceedingJoinPoint->arguments;
        $data = $arguments['keys']['data'];
        $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new DeleteListenerEvent('site-config-update', []));
        foreach ($data as $name => $value) {
            $eventDispatcher->dispatch(new DeleteListenerEvent('system-config-update', ['key' => (string) $name]));
            $this->mapper->updateByKey((string) $name, $value);
        }
        return true;
    }
}

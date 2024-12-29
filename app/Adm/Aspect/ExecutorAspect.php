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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Mine\Exception\NormalStatusException;

/**
 * Aspect for Crontab Executor.
 */
#[Aspect]
class ExecutorAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Crontab\Strategy\Executor',
    ];

    public array $annotations = [
    ];

    /**
     * process.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        try {
            $methodName = $proceedingJoinPoint->methodName;
            if ($methodName === 'logResult') {
                return $this->logResult($proceedingJoinPoint);
            }
            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            logger('SystemUserServiceAspect')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    protected function logResult(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments;
        ['crontab' => $crontab, 'isSuccess' => $isSuccess, 'throwable' => $throwable] = $arguments['keys'];
        $logger = match (true) {
            container()->has(LoggerInterface::class) => container()->get(LoggerInterface::class),
            container()->has(StdoutLoggerInterface::class) => container()->get(StdoutLoggerInterface::class),
            default => null,
        };

        if (! $isSuccess) {
            $logger?->error(sprintf('Crontab task [%s] failed execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
            $throwable && $logger?->error((string) $throwable);
        }
    }
}

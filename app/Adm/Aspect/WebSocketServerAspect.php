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

use Hyperf\Context\Context;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Hyperf\Engine\Http\FdGetter;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\WebSocketServer\Context as WsContext;
use Mine\Exception\NormalStatusException;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Aspect for WebSocketServer.
 */
#[Aspect]
class WebSocketServerAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\WebSocketServer\Server',
    ];

    public array $annotations = [
    ];

    public function __construct(protected StdoutLoggerInterface $logger) {}

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
            if ($methodName === 'onMessage') {
                return $this->onMessage($proceedingJoinPoint);
            }
            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            logger('SystemUserServiceAspect')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Push messages.
     */
    public function onMessage(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments;
        $server = $arguments['keys']['server'];
        $frame = $arguments['keys']['frame'];
        if ($server instanceof WebSocketServer) {
            $fd = $frame->fd;
        } else {
            $fd = container()->get(FdGetter::class)->get($server);
        }
        Context::set(WsContext::FD, $fd);
        $fdObj = FdCollector::get($fd);
        if (! $fdObj) {
            $this->logger->warning(sprintf('WebSocket: fd[%d] does not exist.', $fd));
            // $server->close($fd);
            return;
        }

        $instance = container()->get($fdObj->class);

        if (! $instance instanceof OnMessageInterface) {
            $this->logger->warning("{$instance} is not instanceof " . OnMessageInterface::class);
            return;
        }

        try {
            $instance->onMessage($server, $frame);
        } catch (\Throwable $exception) {
            $this->logger->warning(sprintf('WebSocket: push message on fd[%d] was dropped.', $fd));
        }
    }
}

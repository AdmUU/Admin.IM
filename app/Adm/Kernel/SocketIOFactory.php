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

namespace App\Adm\Kernel;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\SocketIOServer\Parser\Decoder;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;
use Psr\Container\ContainerInterface;

class SocketIOFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $io = new SocketIO(
            $container->get(StdoutLoggerInterface::class),
            $container->get(Sender::class),
            $container->get(Decoder::class),
            $container->get(Encoder::class),
            $container->get(SidProviderInterface::class)
        );

        $io->setPingTimeout(20000);
        $io->setPingInterval(10000);

        return $io;
    }
}

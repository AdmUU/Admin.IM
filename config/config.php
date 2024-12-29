<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

use function Hyperf\Support\env;

return [
    'app_name' => env('APP_NAME', 'Admin.IM'),
    'app_env' => env('APP_ENV', 'prod'),
    'app_debug' => env('APP_DEBUG', false),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),
    StdoutLoggerInterface::class => [
        'log_level' => array_merge([
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ], env('APP_DEBUG', false) ? [LogLevel::DEBUG] : []),
    ],
];

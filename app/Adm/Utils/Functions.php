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
use App\Adm\Interfaces\AdmIpLocationInterface;
use App\Adm\Utils\NetworkUtils;
use Hyperf\Context\RequestContext;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

if (! function_exists('storage_path')) {
    /**
     * Get storage path.
     */
    function storage_path(): string
    {
        return BASE_PATH . '/' . env('STORAGE_PATH', 'storage');
    }
}

if (! function_exists('get_request')) {
    /**
     * Get the current request from the context.
     */
    function get_request(): ?ServerRequestInterface
    {
        try {
            $request = RequestContext::get();
            if ($request instanceof ServerRequestPlusInterface) {
                return $request;
            }
        } catch (Throwable) {
        }

        return null;
    }
}

if (! function_exists('alog')) {
    /**
     * A log function.
     */
    function alog(string|Stringable $message, string $level = 'info', string $name = 'Adm', ?string $ip = null, bool $console_log = false): void
    {
        $ip = $ip ?? NetworkUtils::getClientIp();
        $ipinfo = $ip ?? '';
        // if ($ip != null) {
        //     $ipLocation = container()->get(AdmIpLocationInterface::class);
        //     $location = $ipLocation->search($ip, 'string');
        //     if ($location && $location != 'Unknown') {
        //         $ipinfo = $ip . ' ' . $location;
        //     }
        // }
        $level = in_array(strtolower($level), ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log']) ? strtolower($level) : 'info';
        $message = sprintf('%s %s', $ipinfo, $message);
        if ($console_log) {
            console()->{$level}(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
        }
        logger($name)->{$level}($message);
    }
}

if (! function_exists('exception_log')) {
    /**
     * Exception log.
     */
    function exception_log(Exception $e): void
    {
        alog(sprintf('[%s] %s %s(%s)', $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()), 'error', 'Exception Log', null, true);
    }
}

if (! function_exists('clean_cache')) {
    /**
     * Clean cache.
     */
    function clean_cache(string $key): void
    {
        $redis = redis();
        $prefix = config('cache.default.prefix');
        $iterator = null;
        while (false !== ($cacheKey = $redis->scan($iterator, $prefix . $key, 100))) {
            $redis->del($cacheKey);
        }
    }
}

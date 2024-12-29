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

namespace Plugin\Captcha\Service;

use Hyperf\Di\Annotation\Inject;
use Mine\Exception\NormalStatusException;
use Mine\Redis\MineLockRedis;
use Plugin\Captcha\Exception\VerifyException;
use Plugin\Captcha\Service\Verifier\Angle;
use Plugin\Captcha\Service\Verifier\Slider;
use Psr\SimpleCache\CacheInterface;

class Service
{
    #[Inject]
    private MineLockRedis $locker;

    private int $ttl;

    private int $attempts;

    private string $cachePrefix;

    #[Inject]
    private Angle $angle;

    #[Inject]
    private Slider $slider;

    #[Inject]
    private CacheInterface $cache;

    public function __construct()
    {
        $this->ttl = config('ljk123-captcha.ttl', 60);
        $this->attempts = config('ljk123-captcha.attempts', 2); // 同一个验证允许尝试次数
        $this->cachePrefix = config('ljk123-captcha.cahce_prefix', 'ljk123');
        $this->locker->setTypeName('captchaLock');
    }

    public function create(int $type, string $captchaId)
    {
        if (empty($captchaId)) {
            $captchaId = uniqid('captchaId');
        }
        $option = null;
        switch ($type) {
            case 0:
                $option = $this->angle->create();
                break;
            case 1:
                $option = $this->slider->create();
                break;
        }
        if (is_null($option)) {
            throw new NormalStatusException('Captcha create fail!');
        }
        $this->cache->set($this->cachePrefix . ':' . $captchaId, [
            'option' => $option['server_option'],
            'type' => $type,
            'attempts' => 0,
        ], $this->ttl);
        return ['captchaId' => $captchaId, 'option' => $option['client_option']];
    }

    public function preVerify(int $type, string $captchaId, $verify, string $t)
    {
        // 验证特征
        if (self::checkT($t, $captchaId) === false) {
            $this->cache->delete($this->cachePrefix . ':' . $captchaId);
            throw new VerifyException('captchaIdInvalid'); // 直接还验证码
        }
        $cache = $this->cache->get($this->cachePrefix . ':' . $captchaId);
        if (! $cache) {
            throw new VerifyException('captchaIdInvalid');
        }
        if ($type !== $cache['type']) {
            throw new VerifyException('captchaTypeError');
        }
        // 已经通过验证
        if (! empty($cache['uniqid_key'])) {
            return [
                'key' => $cache['uniqid_key'],
            ];
        }
        // 防止并发尝试 因为允许没多少可能
        if ($this->locker->run(function () use ($type, $cache, $verify, $captchaId, &$ret) {
            switch ($type) {
                case 0:
                    $ret = $this->angle->verify($cache['option'], $verify);
                    break;
                case 1:
                    $ret = $this->slider->verify($cache['option'], $verify);
                    break;
            }
            if ($ret) {
                // 生成一个结果key 并且保存起来
                $key = uniqid();
                $this->cache->set($this->cachePrefix . ':' . $captchaId, [
                    'option' => $cache['option'],
                    'type' => $type,
                    'uniqid_key' => $key,
                ], $this->ttl);
                $ret = [
                    'key' => $key,
                ];
                return;
            }
            if ($cache['attempts'] >= $this->attempts) {
                // 删除cache
                $this->cache->delete($this->cachePrefix . ':' . $captchaId);
                $ret = [
                    'expire' => true,
                ];
                return;
            }
            $this->cache->set($this->cachePrefix . ':' . $captchaId, [
                'option' => $cache['option'],
                'type' => $type,
                'attempts' => $cache['attempts'] + 1,
            ], $this->ttl);
        }, 'preVerify:' . $captchaId, 1) === false) {
            return false; // 没抢到锁
        }
        return $ret;
    }

    public function verify(int $type, string $captchaId, string $verify_key): bool
    {
        $cache = $this->cache->get($this->cachePrefix . ':' . $captchaId);

        if (! $cache) {
            return false;
        }
        if ($type !== $cache['type']) {
            return false;
        }
        // 没通过验证
        if (empty($cache['uniqid_key'])) {
            return false;
        }
        if ($verify_key !== $cache['uniqid_key']) {
            return false;
        }
        // 加锁 只能用一次防止改参数重放请求
        if ($this->locker->run(function () use ($captchaId) {
            $this->cache->delete($this->cachePrefix . ':' . $captchaId);
        }, 'verify:' . $captchaId, 1) === false) {
            return false;
        }
        return true;
    }

    private static function checkT(string $t, string $captcha_id)
    {
        if (strlen($t) < 32) {
            return false;
        }
        if (! $decode = base64_decode($t)) {
            return false;
        }
        $sign = substr($decode, -32);
        if (! $jsonStr = base64_decode(substr($decode, 0, -32))) {
            return false;
        }
        if ($sign !== md5($jsonStr . $captcha_id)) {
            return false;
        }
        if (! $json = json_decode($jsonStr, true)) {
            return false;
        }
        return empty($json['webdriver']) && empty($json['cdcFunction']) && empty($json['headlessChrome']) && empty($json['seleniumIDE']) && empty($json['webdriverEvaluate']) && empty($json['driverEvaluate']) && empty($json['seleniumEvaluate']) && empty($json['webdriverScriptFunction']) && empty($json['webdriverScriptFunc']);
    }
}

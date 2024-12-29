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

namespace Plugin\Captcha\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Mine\MineController;
use Plugin\Captcha\Exception\VerifyException;
use Plugin\Captcha\Service\Service;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'plugin/captcha')]
class IndexController extends MineController
{
    #[Inject]
    private Service $service;

    #[PostMapping('get_captcha')]
    public function getCaptcha(): ResponseInterface
    {
        $type = (int) $this->request->post('type', 0);
        $captchaId = (string) $this->request->post('captcha_id');
        $res = $this->service->create($type, $captchaId);

        echo 'memory usage: ' . memory_get_usage() . PHP_EOL;
        return $this->success($res);
    }

    #[PostMapping('pre_verify')]
    public function preVerify(): ResponseInterface
    {
        $type = (int) $this->request->post('type', 0);
        $captchaId = (string) $this->request->post('captcha_id');
        $verify = $this->request->post('verify');
        $t = (string) $this->request->post('t');
        try {
            if (false === $ret = $this->service->preVerify($type, $captchaId, $verify, $t)) {
                return $this->success([
                    'ret' => false,
                ]);
            }
        } catch (VerifyException $e) {
            return $this->success([
                'msg' => $e->getMessage(),
                'ret' => false,
            ]);
        }
        return $this->success([
            'data' => $ret,
            'ret' => ! empty($ret['key']),
        ]);
    }
}

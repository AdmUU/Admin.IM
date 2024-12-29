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

namespace Plugin\Captcha\Aspect;

use App\System\Controller\LoginController;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Hyperf\HttpServer\Request;
use Mine\Exception\NormalStatusException;
use Mine\Helper\MineCode;
use Plugin\Captcha\Annotation\Captcha;
use Plugin\Captcha\Service\Service;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class CaptchaAspect.
 */
#[Aspect]
class CaptchaAspect extends AbstractAspect
{
    public array $classes = [
        LoginController::class . '::login',
    ];

    public array $annotations = [
        Captcha::class,
    ];

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    private Service $service;

    /**
     * @return mixed
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->className === LoginController::class && $proceedingJoinPoint->methodName === 'login') {
            if (config('ljk123-captcha.close_sys_login_verify')) {
                return $proceedingJoinPoint->process();
            }
        }
        $request = $this->container->get(Request::class);
        $type = (int) $request->post('captcha.type');
        $key = (string) $request->post('captcha.key');
        $captchaId = (string) $request->post('captcha.captcha_id');
        if ($this->service->verify($type, $captchaId, $key)) {
            return $proceedingJoinPoint->process();
        }
        throw new NormalStatusException(t('captcha.verify_fail'), MineCode::VALIDATE_FAILED);
    }
}

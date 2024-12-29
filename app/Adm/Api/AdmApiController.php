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

namespace App\Adm\Api;

use App\Adm\Api\Middleware\VerifyInterfaceMiddleware;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Validation\Annotation\Scene;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\ValidationException;
use Mine\Exception\NoPermissionException;
use Mine\Exception\NormalStatusException;
use Mine\Exception\TokenException;
use Mine\Helper\MineCode;
use Mine\MineApi;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AdmApiController.
 */
#[Controller(prefix: 'adm')]
class AdmApiController extends MineApi
{
    public const SIGN_VERSION = '1.0';

    /**
     * initialize.
     */
    protected function __init()
    {
        if (empty($this->request->input('apiData'))) {
            throw new NormalStatusException(t('mineadmin.access_denied'), MineCode::NORMAL_STATUS);
        }

        return $this->request->input('apiData');
    }

    /**
     * Api v1 version.
     */
    #[RequestMapping('v1/{method}')]
    #[Middlewares([VerifyInterfaceMiddleware::class])]
    public function v1(): ResponseInterface
    {
        $apiData = $this->__init();

        try {
            $class = make($apiData['class_name']);
            $reflectionMethod = ReflectionManager::reflectMethod($apiData['class_name'], $apiData['method_name']);
            $parameters = $reflectionMethod->getParameters();
            $args = [];
            foreach ($parameters as $parameter) {
                if ($parameter->getType() === null) {
                    continue;
                }
                $className = $parameter->getType()->getName();
                $formRequest = container()->get($className);
                $args[] = $formRequest;
                if ($formRequest instanceof FormRequest) {
                    $this->handleSceneAnnotation($formRequest, $apiData['class_name'], $apiData['method_name'], $parameter->getName());
                    $formRequest->validateResolved();
                }
            }
            return $reflectionMethod->invokeArgs($class, $args);
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException) {
                $errors = $e->errors();
                $error = array_shift($errors);
                if (is_array($error)) {
                    $error = array_shift($error);
                }
                throw new NormalStatusException(t('mineadmin.interface_exception') . $error, MineCode::INTERFACE_EXCEPTION);
            }
            if ($e instanceof NoPermissionException) {
                throw new NormalStatusException(t(key: 'mineadmin.api_auth_fail') . $e->getMessage(), code: MineCode::NO_PERMISSION);
            }
            if ($e instanceof TokenException) {
                throw new NormalStatusException(t(key: 'mineadmin.api_auth_exception') . $e->getMessage(), code: MineCode::TOKEN_EXPIRED);
            }

            throw new NormalStatusException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Health check.
     */
    #[RequestMapping('health')]
    public function healthCheck(): ResponseInterface
    {
        try {
            Db::select('SELECT 1');
            redis()->get('health_check');
            return $this->response->json([
                'status' => 'healthy',
                'timestamp' => time(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'timestamp' => time(),
            ], 503);
        }
    }

    /**
     * Handle scene annotation.
     */
    protected function handleSceneAnnotation(FormRequest $request, string $class, string $method, string $argument): void
    {
        /** @var null|MultipleAnnotation $scene */
        $scene = AnnotationCollector::getClassMethodAnnotation($class, $method)[Scene::class] ?? null;
        if (! $scene) {
            return;
        }

        $annotations = $scene->toAnnotations();
        if (empty($annotations)) {
            return;
        }

        /** @var Scene $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation->argument === null || $annotation->argument === $argument) {
                $request->scene($annotation->scene ?? $method);
                return;
            }
        }
    }
}

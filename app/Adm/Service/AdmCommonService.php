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

namespace App\Adm\Service;

use Hyperf\Di\Annotation\Inject;
use Mine\Abstracts\AbstractService;
use Psr\Container\ContainerInterface;

/**
 * Common service.
 */
class AdmCommonService extends AbstractService
{
    #[Inject]
    private ContainerInterface $container;

    /**
     * Call a method of a class.
     *
     * @return mixed
     */
    public function callMethod(string $className, string $methodName, array $params = [])
    {
        if ($this->container->has($className)) {
            $instance = $this->container->get($className);
            if (method_exists($instance, $methodName)) {
                return call_user_func_array([$instance, $methodName], $params);
            }
        }
        return null;
    }
}

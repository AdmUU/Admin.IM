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

namespace App\Adm\Controller\Node;

use App\Adm\Service\AdmRequestStatisticsService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Mine\Annotation\Auth;
use Mine\Annotation\Permission;
use Mine\Middlewares\CheckModuleMiddleware;
use Mine\MineController;
use Psr\Http\Message\ResponseInterface;

/**
 * Request Statistics Controller.
 */
#[Controller(prefix: 'adm/requestStatistics'), Auth]
#[Middleware(middleware: CheckModuleMiddleware::class)]
class AdmRequestStatisticsController extends MineController
{
    #[Inject]
    protected AdmRequestStatisticsService $service;

    /**
     * query request statistics.
     */
    #[GetMapping('query'), Permission('adm:node, adm:node:index')]
    public function query(): ResponseInterface
    {
        return $this->success($this->service->getRecord($this->request->all()));
    }

    /**
     * get phpinfo.
     */
    #[GetMapping('phpinfo'), Permission('adm:node, adm:node:index')]
    public function phpinfo()
    {
        phpinfo();
        $headers = $this->request->getHeaders();
        $serverParams = $this->request->getServerParams();
        \print_r($headers);
        \print_r($serverParams);
    }
}

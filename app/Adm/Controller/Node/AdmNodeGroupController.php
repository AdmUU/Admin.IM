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

use App\Adm\Request\AdmNodeGroupRequest;
use App\Adm\Service\AdmNodeGroupService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Mine\Annotation\Auth;
use Mine\Annotation\OperationLog;
use Mine\Annotation\Permission;
use Mine\Annotation\RemoteState;
use Mine\Middlewares\CheckModuleMiddleware;
use Mine\MineController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Node Group Management Controller.
 */
#[Controller(prefix: 'adm/nodeGroup'), Auth]
#[Middleware(middleware: CheckModuleMiddleware::class)]
class AdmNodeGroupController extends MineController
{
    #[Inject]
    protected AdmNodeGroupService $service;

    /**
     * List.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[GetMapping('index'), Permission('adm:nodeGroup, adm:nodeGroup:index')]
    public function index(): ResponseInterface
    {
        return $this->success($this->service->getPageList($this->request->all()));
    }

    /**
     * Recycle list.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[GetMapping('recycle'), Permission('adm:nodeGroup:recycle')]
    public function recycle(): ResponseInterface
    {
        return $this->success($this->service->getPageListByRecycle($this->request->all()));
    }

    /**
     * RealDelete data.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DeleteMapping('realDelete'), Permission('adm:nodeGroup:realDelete'), OperationLog]
    public function realDelete(): ResponseInterface
    {
        return $this->service->realDelete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * Restore data in the Recycle.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('recovery'), Permission('adm:nodeGroup:recovery'), OperationLog]
    public function recovery(): ResponseInterface
    {
        return $this->service->recovery((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * Save.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PostMapping('save'), Permission('adm:nodeGroup:save'), OperationLog]
    public function save(AdmNodeGroupRequest $request): ResponseInterface
    {
        return $this->success(['id' => $this->service->save($request->all())]);
    }

    /**
     * Update.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('update/{id}'), Permission('adm:nodeGroup:update'), OperationLog]
    public function update(int $id, AdmNodeGroupRequest $request): ResponseInterface
    {
        return $this->service->update($id, $request->all()) ? $this->success() : $this->error();
    }

    /**
     * Read data.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[GetMapping('read/{id}'), Permission('adm:nodeGroup:read')]
    public function read(int $id): ResponseInterface
    {
        return $this->success($this->service->read($id));
    }

    /**
     * Delete.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DeleteMapping('delete'), Permission('adm:nodeGroup:delete'), OperationLog]
    public function delete(): ResponseInterface
    {
        return $this->service->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * Change node group status.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('changeStatus'), Permission('adm:nodeGroup:update'), OperationLog]
    public function changeStatus(): ResponseInterface
    {
        return $this->service->changeStatus(
            (int) $this->request->input('id'),
            (string) $this->request->input('statusValue'),
            (string) $this->request->input('statusName', 'status')
        ) ? $this->success() : $this->error();
    }

    /**
     * Number operation.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('numberOperation'), Permission('adm:nodeGroup:update'), OperationLog]
    public function numberOperation(): ResponseInterface
    {
        return $this->service->numberOperation(
            (int) $this->request->input('id'),
            (string) $this->request->input('numberName'),
            (int) $this->request->input('numberValue', 1),
        ) ? $this->success() : $this->error();
    }

    /**
     * Remote list.
     */
    #[PostMapping('remote'), RemoteState(true)]
    public function remote(): ResponseInterface
    {
        return $this->success($this->service->getRemoteList($this->request->all()));
    }
}

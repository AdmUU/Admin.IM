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

use App\Adm\Request\AdmNodeRequest;
use App\Adm\Service\AdmNodeService;
use App\Adm\Service\AdmSocketService;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Context\ApplicationContext;
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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Node Management Controller.
 */
#[Controller(prefix: 'adm/node'), Auth]
#[Middleware(middleware: CheckModuleMiddleware::class)]
class AdmNodeController extends MineController
{
    #[Inject]
    protected AdmNodeService $service;

    /**
     * List.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[GetMapping('index'), Permission('adm:node, adm:node:index')]
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
    #[GetMapping('recycle'), Permission('adm:node:recycle')]
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
    #[DeleteMapping('realDelete'), Permission('adm:node:realDelete'), OperationLog]
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
    #[PutMapping('recovery'), Permission('adm:node:recovery'), OperationLog]
    public function recovery(): ResponseInterface
    {
        return $this->service->recovery((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * Update.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('update/{id}'), Permission('adm:node:update'), OperationLog]
    public function update(int $id, AdmNodeRequest $request): ResponseInterface
    {
        return $this->service->update($id, $request->all()) ? $this->success() : $this->error();
    }

    /**
     * Delete.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DeleteMapping('delete'), Permission('adm:node:delete'), OperationLog]
    public function delete(): ResponseInterface
    {
        return $this->service->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * Change node status.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('changeStatus'), Permission('adm:node:update'), OperationLog]
    public function changeStatus(): ResponseInterface
    {
        return $this->service->changeStatus(
            (int) $this->request->input('id'),
            (string) $this->request->input('statusValue'),
            (string) $this->request->input('statusName', 'status')
        ) ? $this->success() : $this->error();
    }

    /**
     * Read data.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[GetMapping('read/{id}'), Permission('adm:node:read')]
    public function read(int $id): ResponseInterface
    {
        return $this->success($this->service->read($id));
    }

    /**
     * Number operation.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('numberOperation'), Permission('adm:node:update'), OperationLog]
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

    /**
     * Enable/Disable a node.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('enable/{id}'), Permission('adm:node:update'), OperationLog]
    public function enable(int $id, AdmNodeRequest $request): ResponseInterface
    {
        $enable = $request->input('enable');
        if ($enable != 0 && $enable != 1) {
            return $this->error();
        }
        $node = $this->service->read($id);
        if ($enable == 0 && $node['online_status'] == 1) {
            $socketService = container()->get(AdmSocketService::class);
            $socketService->nodeDisconnect([$id]);
        }
        $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $node['auth_code'], 'ignoreBlock' => true]));
        $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $node['auth_code'], 'ignoreBlock' => false]));
        return $this->service->update($id, ['enable' => $enable]) ? $this->success() : $this->error();
    }

    /**
     * Block/UnBlock.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('block/{id}'), Permission('adm:node:update'), OperationLog]
    public function block(int $id, AdmNodeRequest $request): ResponseInterface
    {
        $block = $request->input('block');
        if ($block != 0 && $block != 1) {
            return $this->error();
        }
        $node = $this->service->read($id);
        if ($block == 1 && $node['online_status'] == 1) {
            $socketService = container()->get(AdmSocketService::class);
            $socketService->nodeDisconnect([$id], 'block');
        }
        $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $node['auth_code'], 'ignoreBlock' => true]));
        $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $node['auth_code'], 'ignoreBlock' => false]));
        return $this->service->update($id, ['block' => $block]) ? $this->success() : $this->error();
    }

    /**
     * Upgrade node.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[PutMapping('upgrade'), Permission('adm:node:update'), OperationLog]
    public function upgrade(): ResponseInterface
    {
        $socketService = container()->get(AdmSocketService::class);
        return $socketService->nodeUpdate((array) $this->request->input('ids', [])) ? $this->success() : $this->error(t('adm.no_nodes_to_update'));
    }
}

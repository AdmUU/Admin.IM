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

use App\Adm\Interfaces\AdmIpLocationInterface;
use App\Adm\Mapper\AdmNodeMapper;
use App\Adm\Task\OnlineTimeTask;
use App\Adm\Utils\AdmCode;
use App\Adm\Utils\LangUtils;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Mine\Abstracts\AbstractService;
use Mine\Exception\NormalStatusException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Node service.
 */
class AdmNodeService extends AbstractService
{
    /**
     * @var AdmNodeMapper
     */
    public $mapper;

    #[Inject]
    protected AdmSystemDictDataService $dictDataService;

    #[Inject]
    protected AdmAuthService $authService;

    #[Inject]
    private AdmNodeGroupService $nodeGroupService;

    #[Inject]
    private AdmIpLocationInterface $ipLocation;

    public function __construct(AdmNodeMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get node page list.
     */
    public function getPageList(?array $params = null, bool $isScope = true): array
    {
        if (! isset($params['orderBy'])) {
            $params['orderBy'] = ['enable', 'online_status', 'created_at'];
            $params['orderType'] = ['desc', 'desc', 'desc'];
        }
        $list = parent::getPageList($params);
        $list['items'] = array_map(function ($item) {
            $item->group = $item->group;
            return $item;
        }, $list['items']);
        return $list;
    }

    /**
     * Delete nodes.
     *
     * @param array $ids IDs of items to be deleted
     */
    public function delete(array $ids): bool
    {
        $socketService = container()->get(AdmSocketService::class);
        $socketService->nodeDisconnect($ids, 'block');
        $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        foreach ($ids as $id) {
            $auth_code = $this->mapper->value([['id', $id]], 'auth_code');
            $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $auth_code, 'ignoreBlock' => true]));
            $eventDispatcher->dispatch(new DeleteListenerEvent('node-enable', ['authCode' => $auth_code, 'ignoreBlock' => false]));
        }
        return parent::delete($ids);
    }

    /**
     * Get node by ID.
     *
     * @param int $id ID of item to be retrieved
     */
    public function get($id): array|bool
    {
        return $this->mapper->getNodeByID($id);
    }

    /**
     * Create a node.
     *
     * @param array $params Parameters to be saved
     * @return mixed Result of saving parameters
     */
    public function save(array $params): mixed
    {
        $name = '';
        $name_en = '';
        $isp = '';
        $did = '';

        $ipNode = $this->checkNodeByIp((string) $params['ipv4'], (string) $params['ipv6']);
        if (! empty($ipNode)) {
            if (count(array_filter($ipNode, fn ($item) => $item['online_status'] == 1)) > 0) {
                throw new NormalStatusException(t(key: 'adm.ip_regist_exist'), AdmCode::IP_REGIST_FAILD);
            }
            parent::delete(array_column($ipNode, 'id'));
        }
        if (! $nodegroup = $this->nodeGroupService->getGroupByClientID($params['key'])) {
            throw new NormalStatusException(t(key: 'adm.client_id_verification_fail'), AdmCode::API_AUTH_CLIENT_ID_FAILD);
        }
        $params['ipLocation'] = $this->ipLocation->search($params['ip']);

        $nodegroup['share_type'] < 100 && $isp = $params['ipLocation']['isp'] ?: 'prv';
        $auth_code = $this->generateAuthCode();

        $site_language = sys_config('site_language')['value'];
        // $name_en = LangUtils::getEnName($params['ipLocation']['country'], 'country');
        $name_en = $params['ipLocation']['country'];
        $name = $site_language == 'zh-CN' ? LangUtils::getCnName($params['ipLocation']['country'], 'country') : $name_en;
        if (! empty($params['ipLocation']['province'])) {
            $province_name = $site_language == 'zh-CN' ? LangUtils::getCnName($params['ipLocation']['province'], 'province', $params['ipLocation']['country']) : $params['ipLocation']['province'];
            if (in_array($isp, ['ct', 'cu', 'cm', 'cb', 'ce', 'cs', 'cbg'])) {
                $isps = $this->dictDataService->getIspList('details');
                $name = $province_name . $isps[$isp]['title'];
                $name_en = $name_en . '-' . ucfirst($params['ipLocation']['province']) . '(' . strtoupper($isp) . ')';
            } else {
                $name = $name . $province_name;
                $name_en = $name_en . '-' . ucfirst($params['ipLocation']['province']);
            }
        }
        $did = snowflake_id();
        $sponsor_data = $this->handleSponsor($params, $nodegroup['share_type']);

        $data = [
            'adm_node_group_id' => $nodegroup['id'],
            'did' => $did,
            'name' => $name,
            'name_en' => $name_en,
            'ipv4' => $params['ipv4'] ?? '',
            'ipv6' => $params['ipv6'] ?? '',
            'country' => $params['ipLocation']['country'],
            'province' => $params['ipLocation']['province'],
            'region' => $params['ipLocation']['region'],
            'continent' => $params['ipLocation']['continent'],
            'isp' => $isp,
            'version' => $params['version'],
            'fingerprint' => $params['fingerprint'],
            'connection_type' => $sponsor_data['connection_type'],
            'sponsor_id' => $sponsor_data['sponsor_id'],
            'sponsor_name' => $sponsor_data['sponsor_name'],
            'sponsor_url' => $sponsor_data['sponsor_url'],
            'sponsor_status' => $sponsor_data['sponsor_status'],
            'auth_code' => $auth_code,
        ];
        if (! $this->mapper->save($data)) {
            throw new NormalStatusException(t(key: 'adm.database_fail'), AdmCode::DATABASE_FAILD);
        }
        return ['auth_code' => $auth_code, 'did' => $did];
    }

    /**
     * Get node list.
     */
    public function getNodeList(?array $select, ?array $params = null): array
    {
        if (! isset($params['enable'])) {
            $params['enable'] = 1;
        }
        if (! isset($params['online_status'])) {
            $params['online_status'] = 1;
        }
        return $this->mapper->getNodeList($select, $params);
    }

    /**
     * Update agent node.
     *
     * @param array $node Node data
     * @param array $params Parameters to be updated
     */
    public function updateAgent(array $node, array $params): mixed
    {
        $updateData = [];
        if (isset($params['ipv4']) && $params['ipv4'] !== $node['ipv4']) {
            $updateData['ipv4'] = $params['ipv4'];
        }
        if (isset($params['ipv6']) && $params['ipv6'] !== $node['ipv6']) {
            $updateData['ipv6'] = $params['ipv6'];
        }
        if (isset($params['version']) && $params['version'] !== $node['version']) {
            $updateData['version'] = $params['version'];
        }
        if (isset($params['sponsor_id']) && ! empty($params['sponsor_id'] && empty($node['sponsor_id']))) {
            $updateData['sponsor_id'] = $params['sponsor_id'];
        }
        if (! empty($updateData)) {
            return $this->update($node['id'], $updateData);
        }
        return null;
    }

    /**
     * Check and retrieve a node by its IP addresses.
     *
     * @param string $ipv4 the IPv4 address of the node
     * @param string $ipv6 the IPv6 address of the node (optional)
     * @return null|array the node data if found, null otherwise
     * @throws NormalStatusException
     */
    public function checkNodeByIP(string $ipv4, string $ipv6 = ''): ?array
    {
        $node = $this->mapper->getNodeByIP($ipv4, $ipv6);
        if ($node === false) {
            throw new NormalStatusException(t(key: 'adm.ip_regist_fail'), AdmCode::IP_REGIST_FAILD);
        }
        return $node;
    }

    /**
     * Generate an auth code.
     *
     * @throws NormalStatusException
     */
    public function generateAuthCode(): string
    {
        $ipKey = Context::get('ipKey');
        $generateAuthCodeKey = 'apiLimit:authCode:' . $ipKey;
        $redis = redis();
        if (! $redis->exists($generateAuthCodeKey)) {
            $redis->set($generateAuthCodeKey, 0, ['nx', 'ex' => 86400]);
        }
        $generateNum = $redis->incr($generateAuthCodeKey);
        if ($generateNum > 20) {
            throw new NormalStatusException(t(key: 'adm.too_many_regist') . $generateAuthCodeKey, AdmCode::TOO_MANY_REGIST);
        }
        return bin2hex(random_bytes(8));
    }

    /**
     * Handle sponsor data.
     */
    public function handleSponsor(array $params, int $share_type): array
    {
        $sponsor_data = [
            'connection_type' => 1,
            'sponsor_id' => null,
            'sponsor_name' => null,
            'sponsor_url' => null,
            'sponsor_status' => 'review',
        ];
        if ($share_type === 0) {
            return $sponsor_data;
        }
        if (! empty($params['sponsor'])) {
            $sponsor_data['connection_type'] = 2;
            $sponsor_data['sponsor_name'] = mb_substr($params['sponsor'], 0, 8);
        }
        if (! empty($params['sponsor_id'])) {
            $sponsor_data['connection_type'] = 3;
            $sponsor_data['$sponsor_id'] = $params['sponsor_id'];
        }
        if (! empty($params['sponsor_url'])) {
            $sponsor_data['sponsor_url'] = $params['sponsor_url'];
        }
        return $sponsor_data;
    }

    /**
     * Reset node status.
     */
    public function resetNodeStatus(): void
    {
        try {
            $redis = redis();
            $redis->del('node:disconnect');
            $redis->del('node:sid');
            container()->get(OnlineTimeTask::class)->statistics(true);
            $this->mapper->resetNodeStatus();
        } catch (\Exception $e) {
            exception_log($e);
        }
    }

    /**
     * Update online status.
     *
     * @param int $id Node ID
     * @param int $status Online status (1 for online, 0 for offline)
     */
    public function updateOnlineStatus(int $id, int $status): void
    {
        if (! $this->get($id)) {
            return;
        }
        $data = $status === 1 ? ['online_status' => 1, 'online_last_time' => date('Y-m-d H:i:s')] : ['online_status' => 0];
        $this->update($id, $data);
        clean_cache('adm:nodelist:*');
    }

    /**
     * Take a snapshot for node list.
     *
     * @param array $data Node data
     */
    public function snapshotDictNode($data): array
    {
        $list = [];
        try {
            $nodeList = ['items' => []];
            $taskType = $data['task_type'];
            $taskId = $data['task_id'];
            if (! empty($taskType) && ! empty($taskId)) {
                $task = $this->authService->getSocketTask(
                    $taskType,
                    $taskId
                );
                if ($task && $task['client_type'] === 'web') {
                    $nodeList = $this->dictDataService->getNodeList($task['prefer_ip_type'], $data['dict_code'], $data['dict_value']);
                    $snapshotKey = 'snapshot:dictnode:' . \substr(md5(implode(',', $nodeList['ids'])), 0, 8);
                    $redis = redis();
                    $redis->set($snapshotKey, serialize($nodeList['ids']), ['nx', 'ex' => 3600]);
                    $task['node_snapshot'] = $snapshotKey;
                    $this->authService->setSocketTask($taskType, $task, $taskId);
                }
            } else {
                $nodeList = $this->dictDataService->getNodeList($data['prefer_ip_type'], $data['dict_code'], $data['dict_value']);
            }
            $list = $nodeList['items'];
        } catch (\Exception $e) {
            exception_log($e);
        }
        return $list;
    }
}

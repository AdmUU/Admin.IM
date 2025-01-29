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

namespace App\Adm\Mapper;

use App\Adm\Model\AdmNode;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\AbstractMapper;

/**
 * Class AdmNodeMapper.
 */
class AdmNodeMapper extends AbstractMapper
{
    /**
     * @var AdmNode
     */
    public $model;

    public function assignModel()
    {
        $this->model = AdmNode::class;
    }

    /**
     * Search handler.
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        // ID
        if (isset($params['id']) && filled($params['id'])) {
            $query->where('id', '=', $params['id']);
        }

        // Distributed ID
        if (isset($params['did']) && filled($params['did'])) {
            $query->where('did', '=', $params['did']);
        }

        // Node group
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('adm_node_group_id', '=', $params['group_id']);
        }

        // Node name
        if (isset($params['name']) && filled($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        // English name
        if (isset($params['name_en']) && filled($params['name_en'])) {
            $query->where('name_en', 'like', '%' . $params['name_en'] . '%');
        }

        // Enable
        if (isset($params['enable']) && filled($params['enable'])) {
            $query->where('enable', '=', $params['enable']);
        }

        // IPv4
        if (isset($params['ipv4']) && filled($params['ipv4'])) {
            $query->where('ipv4', '=', $params['ipv4']);
        }

        // IPv6
        if (isset($params['ipv6']) && filled($params['ipv6'])) {
            $query->where('ipv6', '=', $params['ipv6']);
        }

        // Country
        if (isset($params['country']) && filled($params['country'])) {
            $query->where('country', 'like', '%' . $params['country'] . '%');
        }

        // Province
        if (isset($params['province']) && filled($params['province'])) {
            $query->where('province', 'like', '%' . $params['province'] . '%');
        }

        // Region
        if (isset($params['region']) && filled($params['region'])) {
            $query->where('region', 'like', '%' . $params['region'] . '%');
        }

        // Continent
        if (isset($params['continent']) && filled($params['continent'])) {
            $query->where('continent', 'like', '%' . $params['continent'] . '%');
        }

        // ISP
        if (isset($params['isp']) && filled($params['isp'])) {
            $query->where('isp', 'like', '%' . $params['isp'] . '%');
        }

        // Online status
        if (isset($params['online_status']) && filled($params['online_status'])) {
            $query->where('online_status', '=', $params['online_status']);
        }

        // Online time
        if (isset($params['online_total_time']) && filled($params['online_total_time'])) {
            $query->where('online_total_time', '=', $params['online_total_time']);
        }

        // Online last time
        if (isset($params['online_last_time']) && filled($params['online_last_time']) && is_array($params['online_last_time']) && count($params['online_last_time']) == 2) {
            $query->whereBetween(
                'online_last_time',
                [$params['online_last_time'][0], $params['online_last_time'][1]]
            );
        }

        // Weight
        if (isset($params['weight']) && filled($params['weight'])) {
            $query->where('weight', '=', $params['weight']);
        }

        // Version
        if (isset($params['version']) && filled($params['version'])) {
            $query->where('version', '=', $params['version']);
        }

        // Connection type
        if (isset($params['connection_type']) && filled($params['connection_type'])) {
            $query->where('connection_type', '=', $params['connection_type']);
        }

        // Auth code
        if (isset($params['auth_code']) && filled($params['auth_code'])) {
            $query->where('auth_code', 'like', '%' . $params['auth_code'] . '%');
        }

        // Sponsor ID
        if (isset($params['sponsor_id']) && filled($params['sponsor_id'])) {
            $query->where('sponsor_id', '=', $params['sponsor_id']);
        }

        // Sponsor
        if (isset($params['sponsor_name']) && filled($params['sponsor_name'])) {
            $query->where('sponsor_name', 'like', '%' . $params['sponsor_name'] . '%');
        }

        // Sponsor link
        if (isset($params['sponsor_url']) && filled($params['sponsor_url'])) {
            $query->where('sponsor_url', 'like', '%' . $params['sponsor_url'] . '%');
        }

        // Sponsor status
        if (isset($params['sponsor_status']) && filled($params['sponsor_status'])) {
            $query->where('sponsor_status', '=', $params['sponsor_status']);
        }

        // Ext
        if (isset($params['ext']) && filled($params['ext'])) {
            $query->where('ext', 'like', '%' . $params['ext'] . '%');
        }

        // creator
        if (isset($params['created_by']) && filled($params['created_by'])) {
            $query->where('created_by', '=', $params['created_by']);
        }

        // updater
        if (isset($params['updated_by']) && filled($params['updated_by'])) {
            $query->where('updated_by', '=', $params['updated_by']);
        }

        // Creation time
        if (isset($params['created_at']) && filled($params['created_at']) && is_array($params['created_at']) && count($params['created_at']) == 2) {
            $query->whereBetween(
                'created_at',
                [$params['created_at'][0], $params['created_at'][1]]
            );
        }

        // Update time
        if (isset($params['updated_at']) && filled($params['updated_at']) && is_array($params['updated_at']) && count($params['updated_at']) == 2) {
            $query->whereBetween(
                'updated_at',
                [$params['updated_at'][0], $params['updated_at'][1]]
            );
        }

        // Delete time
        if (isset($params['deleted_at']) && filled($params['deleted_at']) && is_array($params['deleted_at']) && count($params['deleted_at']) == 2) {
            $query->whereBetween(
                'deleted_at',
                [$params['deleted_at'][0], $params['deleted_at'][1]]
            );
        }

        // comment
        if (isset($params['remark']) && filled($params['remark'])) {
            $query->where('remark', 'like', '%' . $params['remark'] . '%');
        }

        return $query;
    }

    /**
     * Get a list of nodes.
     *
     * @param null|array $select The selected field list, if null, the default field list is used
     * @return array Node record array
     */
    public function getNodeList(?array $select, ?array $params): array
    {
        if (! $select) {
            $select = ['id', 'name', 'name_en', 'ipv4', 'ipv6', 'country', 'province', 'region', 'continent', 'isp', 'online_total_time', 'online_last_time', 'version', 'sponsor_name', 'sponsor_status', 'created_at'];
        }
        // return $this->model::query()->with('isp')->where(['enable' => 1, 'online_status' => 1])->select($select)
        //     ->orderBy('weight', 'desc')
        //     ->orderBy('name_en')
        //     ->get()->toArray();
        return $this->handleSearch($this->model::query(), $params)->select($select)->get()->toArray();
    }

    /**
     * Get node information by ID.
     *
     * @param int $id unique identifier of the node
     * @return array|bool array of node information or false if not found or error
     */
    public function getNodeByID(int $id): array|bool
    {
        try {
            $node = $this->model::query()
                ->where(['id' => $id])
                ->select(['id', 'did'])->get()->toArray();
        } catch (\Exception $e) {
            logger('DB log')->error(sprintf('%s %s[%s] in %s', $e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()));
            echo $e->getMessage();
            $node = false;
        }
        return $node ?? false;
    }

    /**
     * Get node information based on IPv4 or IPv6 address.
     *
     * @param string $ipv4 IPv4 address string
     * @param string $ipv6 IPv6 address string
     * @return array|bool Returns an array if a matching node is found, otherwise returns false
     */
    public function getNodeByIP(string $ipv4, string $ipv6): null|array|bool
    {
        $ipv4 = ip2long($ipv4);
        $ipv6 = inet_pton($ipv6);
        if (empty($ipv4) && empty($ipv6)) {
            return false;
        }
        $result = $this->model::query()
            ->where(function ($query) use ($ipv4, $ipv6) {
                if (! empty($ipv4)) {
                    $query->orWhere('ipv4', '=', $ipv4);
                }
                if (! empty($ipv6)) {
                    $query->orWhere('ipv6', '=', $ipv6);
                }
            })->get();
        return $result ? $result->toArray() : null;
    }

    /**
     * Get a single node information based on the authorization code.
     *
     * @param string $authCode The authorization code used for query
     * @param bool $ignoreBlock Whether to ignore the disabled state of the node, the default is false
     * @return null|array Returns an array containing node information, or null if no matching node is found
     * @throws \Exception May throw exceptions related to database queries
     */
    public function getNodeByAuthCode(string $authCode, bool $ignoreBlock = false): ?array
    {
        $query = $this->model::query();

        if (! $ignoreBlock) {
            $query->where(['enable' => 1, 'block' => 0]);
        }

        $result = $query->where('auth_code', '=', $authCode)->first();

        return $result ? $result->toArray() : null;
    }

    /**
     * Get node information through fingerprint.
     *
     * @param string $fingerprint The unique fingerprint information of the node is used to identify a specific node
     * @return array|bool The node information array, including id and did, or false is returned when there is no result in the query
     */
    public function getNodeByFingerprint(string $fingerprint): array|bool
    {
        return $this->model::query()
            ->where(['enable' => 1])
            ->where('fingerprint', '=', $fingerprint)
            ->select(['id', 'did'])->get()->toArray();
    }

    /**
     * Reset node status.
     */
    public function resetNodeStatus(): void
    {
        try {
            $this->model::query()->update(['online_status' => 0]);
        } catch (\Exception $e) {
            exception_log($e);
        }
    }
}

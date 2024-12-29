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

use App\Adm\Model\AdmSystemDictData;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\AbstractMapper;

/**
 * Class SystemUserMapper.
 */
class SystemDictDataMapper extends AbstractMapper
{
    /**
     * @var SystemDictData
     */
    public $model;

    public function assignModel()
    {
        $this->model = AdmSystemDictData::class;
    }

    /**
     * Search handler.
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['type_id']) && filled($params['type_id'])) {
            $query->where('type_id', $params['type_id']);
        }
        if (isset($params['code']) && filled($params['code'])) {
            $query->where('code', $params['code']);
        }
        if (isset($params['value']) && filled($params['value'])) {
            $query->where('value', 'like', '%' . $params['value'] . '%');
        }
        if (isset($params['label']) && filled($params['label'])) {
            $query->where('label', 'like', '%' . $params['label'] . '%');
        }
        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query;
    }

    /**
     * Get a list of nodes base on ISP code.
     */
    public function getNodeList(array $params = [], ?array $select = null): array
    {
        if (! $select) {
            $select = ['id', 'did', 'name', 'name_en', 'ipv4', 'ipv6', 'country', 'province', 'region', 'continent', 'isp', 'online_total_time', 'online_last_time', 'sponsor_name', 'sponsor_status'];
        }
        return $this->model::has('nodes')->with(['nodes' => function ($query) use ($params, $select) {
            $address_type = $params['address_type'] ?? null;
            $country = $params['country'] ?? null;
            $region = $params['region'] ?? null;
            $continent = $params['continent'] ?? null;
            $isp = $params['isp'] ?? null;
            $query->where(['enable' => 1, 'online_status' => 1])->select($select);
            if ($address_type == 'ipv4') {
                $query->whereNotNull('ipv4');
            } elseif ($address_type == 'ipv6') {
                $query->whereNotNull('ipv6');
            }
            if ($country != null) {
                $query->where('country', $isp);
            }
            if ($region != null) {
                $query->where('region', $isp);
            }
            if ($continent != null) {
                $query->where('continent', $isp);
            }
            if ($isp != null) {
                $query->where('isp', $isp);
            }
            $query->orderBy('name_en');
            $query->orderBy('weight', 'desc');
        }])->where('code', 'ISP')->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
    }
}

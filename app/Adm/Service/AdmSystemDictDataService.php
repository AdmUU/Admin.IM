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

use App\Adm\Mapper\SystemDictDataMapper;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheAhead;
use Mine\Abstracts\AbstractService;
use Mine\MineModel;

/**
 * System DictData Service.
 */
class AdmSystemDictDataService extends AbstractService
{
    /**
     * @var SystemDictDataMapper
     */
    public $mapper;

    public function __construct(SystemDictDataMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get DictData list.
     */
    #[Cacheable(prefix: 'adm:dict:data', value: '_#{params.code}', ttl: 600, listener: 'system-dict-update')]
    public function getList(?array $params = [], bool $isScope = false): array
    {
        $args = [
            'select' => ['id', 'label as title', 'label_en', 'value as key', 'code', 'remark'],
            'status' => MineModel::ENABLE,
            'orderBy' => 'sort',
            'orderType' => 'desc',
        ];
        return $this->mapper->getList(array_merge($args, $params), $isScope);
    }

    /**
     * Get DictData pairs.
     */
    #[Cacheable(prefix: 'adm:dict:pairs', ttl: 600, listener: 'system-dict-update')]
    public function getDictPairs(): array
    {
        $dictData = $this->getList();
        $list = [];
        foreach ($dictData as $dict) {
            $list[$dict['code']][$dict['key']] = $dict['title'];
        }
        return $list;
    }

    /**
     * Get countries list.
     */
    #[Cacheable(prefix: 'adm:dict:countries', ttl: 3600, listener: 'system-dict-update')]
    public function getCountriesList(): array
    {
        $dictData = $this->getList(['code' => 'country_code']);
        $list = [];
        foreach ($dictData as $dict) {
            $list[$dict['key']] = $dict['title'];
        }
        return $list;
    }

    /**
     * Get ISP list.
     */
    #[Cacheable(prefix: 'adm:dict:isp', ttl: 3600, listener: 'system-dict-update')]
    public function getIspList(): array
    {
        $ispDictData = $this->getList(['code' => 'ISP']);
        $isp = [];
        foreach ($ispDictData as $dict) {
            $isp[\strtolower($dict['key'])] = $dict['remark'] ? explode('|', $dict['remark']) : [];
        }
        return $isp;
    }

    /**
     * Get node list.
     */
    #[CacheAhead(prefix: 'adm:nodelist', value: '_#{address_type}_#{dict_code}_#{dict_value}', ttl: 120, aheadSeconds: 30, lockSeconds: 10)]
    public function getNodeList(?string $address_type = null, ?string $dict_code = null, ?string $dict_value = null): array
    {
        $nodeList = [];
        $items = [];
        $params = [
            'address_type' => $address_type,
        ];
        if (! empty($dict_code) && ! empty($dict_value)) {
            $dictPairs = $this->getDictPairs();
            if (isset($dictPairs[$dict_code]) && array_key_exists(strtolower($dict_value), array_map('strtolower', $dictPairs[$dict_code]))) {
                switch ($dict_code) {
                    case 'country_code':
                        $params['country'] = strtoupper($dict_value);
                        break;
                    case 'region':
                        $params['region'] = $dict_value;
                        break;
                    case 'continent_code':
                        $params['continent'] = strtoupper($dict_value);
                        break;
                    case 'isp':
                        $params['isp'] = $dict_value;
                        break;
                }
            }
        }
        $list = $this->mapper->getNodeList($params);
        foreach ($list as $isp) {
            $items = array_merge($items, $isp['nodes']);
        }
        $nodeList['items'] = array_map(function ($node) {
            if (! empty($node['ipv4'])) {
                $node['ip'] = preg_replace('/\.\d+\.\d+\.\d+/', '.*.*.*', $node['ipv4']);
            } elseif (! empty($node['ipv6'])) {
                $node['ip'] = preg_replace('/:.*/', '::*', $node['ipv6']);
            }
            if (! empty($node['sponsor_name']) && $node['sponsor_status'] == 'approval') {
                $node['name'] = $node['name'] . ' (' . $node['sponsor_name'] . ')';
            }
            unset($node['id'], $node['ipv4'], $node['ipv6'], $node['sponsor_name'], $node['sponsor_status']);
            return $node;
        }, $items);
        $nodeList['ids'] = array_map(function ($node) {
            return $node['id'];
        }, $items);
        sort($nodeList['ids']);
        return $nodeList;
    }
}

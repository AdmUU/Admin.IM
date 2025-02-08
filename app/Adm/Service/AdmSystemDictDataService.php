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
use App\Adm\Utils\LangUtils;
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
            $list[$dict['code']][\strtolower($dict['key'])] = $dict['title'];
        }
        return $list;
    }

    /**
     * Get countries list.
     */
    #[Cacheable(prefix: 'adm:dict:countries', value: '_#{type}', ttl: 3600, listener: 'system-dict-update')]
    public function getCountriesList($type = 'pairs'): array
    {
        $dictData = $this->getList(['code' => 'country_code']);
        $list = [];
        foreach ($dictData as $dict) {
            if ($type === 'details') {
                $list[$dict['key']] = $dict;
            } else {
                $list[$dict['key']] = $dict['title'];
            }
        }
        return $list;
    }

    /**
     * Get ISP list.
     */
    #[Cacheable(prefix: 'adm:dict:isp', value: '_#{type}', ttl: 3600, listener: 'system-dict-update')]
    public function getIspList($type = 'remark'): array
    {
        $ispDictData = $this->getList(['code' => 'ISP']);
        $isp = [];
        foreach ($ispDictData as $dict) {
            if ($type === 'details') {
                $isp[\strtolower($dict['key'])] = $dict;
            } else {
                $isp[\strtolower($dict['key'])] = $dict['remark'] ? explode('|', $dict['remark']) : [];
            }
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
        $countries = $this->getCountriesList('details');
        $isps = $this->getIspList('details');
        foreach ($list as $isp) {
            $items = array_merge($items, $isp['nodes']);
        }
        $nodeList['items'] = array_map(function ($node) use ($countries, $isps) {
            $node['country_name'] = '';
            $node['country_name_en'] = '';
            $node['isp_name'] = '';
            $node['isp_name_en'] = '';
            $node['province_name'] = '';
            if (isset($countries[$node['country']])) {
                $node['country_name'] = $countries[$node['country']]['title'];
                $node['country_name_en'] = $countries[$node['country']]['label_en'];
            }
            if (isset($isps[$node['isp']])) {
                $node['isp_name'] = $isps[$node['isp']]['title'];
                $node['isp_name_en'] = $isps[$node['isp']]['label_en'];
            }
            if (isset(LangUtils::CN_PROVINCE[$node['province']])) {
                $node['province_name'] = LangUtils::CN_PROVINCE[$node['province']];
            }
            if (! empty($node['ipv4'])) {
                $node['ip'] = preg_replace('/\.\d+\.\d+\.\d+/', '.*.*.*', $node['ipv4']);
            } elseif (! empty($node['ipv6'])) {
                $node['ip'] = preg_replace('/:.*/', '::*', $node['ipv6']);
            }
            if (! empty($node['sponsor_name']) && $node['sponsor_status'] == 'approval') {
                $node['name'] = $node['name'] . ' (' . $node['sponsor_name'] . ')';
            }
            unset($node['id'], $node['ip'], $node['ipv4'], $node['ipv6'], $node['sponsor_name'], $node['sponsor_status']);
            ksort($node);
            return $node;
        }, $items);
        $nodeList['ids'] = array_map(function ($node) {
            return $node['id'];
        }, $items);
        sort($nodeList['ids']);
        return $nodeList;
    }
}

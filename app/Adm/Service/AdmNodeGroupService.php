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

use App\Adm\Mapper\AdmNodeGroupMapper;
use Hyperf\Cache\Annotation\Cacheable;
use Mine\Abstracts\AbstractService;

/**
 * Node group service.
 */
class AdmNodeGroupService extends AbstractService
{
    /**
     * @var AdmNodeGroupMapper
     */
    public $mapper;

    public function __construct(AdmNodeGroupMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get node group by clientID.
     */
    #[Cacheable(prefix: 'adm:cache:nodeGroup:clientID', value: '_#{clientID}', ttl: 60)]
    public function getGroupByClientID(string $clientID): ?array
    {
        return $this->mapper->getNodeGroupByClientID($clientID);
    }

    /**
     * Get page list.
     */
    public function getPageList(?array $params = null, bool $isScope = true): array
    {
        $list = parent::getPageList($params);
        $list['items'] = array_map(function ($item) {
            $item->node_total = count($item->node);
            return $item;
        }, $list['items']);
        return $list;
    }
}

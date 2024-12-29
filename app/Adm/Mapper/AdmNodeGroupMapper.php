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

use App\Adm\Model\AdmNodeGroup;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\AbstractMapper;

/**
 * Class AdmNodeGroupMapper.
 */
class AdmNodeGroupMapper extends AbstractMapper
{
    /**
     * @var AdmNodeGroup
     */
    public $model;

    public function assignModel()
    {
        $this->model = AdmNodeGroup::class;
    }

    /**
     * Search handler.
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['id']) && filled($params['id'])) {
            $query->where('id', '=', $params['id']);
        }

        if (isset($params['name']) && filled($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['name_en']) && filled($params['name_en'])) {
            $query->where('name_en', 'like', '%' . $params['name_en'] . '%');
        }

        if (isset($params['enable']) && filled($params['enable'])) {
            $query->where('enable', '=', $params['enable']);
        }

        if (isset($params['weight']) && filled($params['weight'])) {
            $query->where('weight', '=', $params['weight']);
        }

        if (isset($params['client_id']) && filled($params['client_id'])) {
            $query->where('client_id', '=', $params['client_id']);
        }

        if (isset($params['client_secret']) && filled($params['client_secret'])) {
            $query->where('client_secret', '=', $params['client_secret']);
        }

        if (isset($params['ext']) && filled($params['ext'])) {
            $query->where('ext', '=', $params['ext']);
        }

        if (isset($params['created_at']) && filled($params['created_at']) && is_array($params['created_at']) && count($params['created_at']) == 2) {
            $query->whereBetween(
                'created_at',
                [$params['created_at'][0], $params['created_at'][1]]
            );
        }

        if (isset($params['updated_at']) && filled($params['updated_at']) && is_array($params['updated_at']) && count($params['updated_at']) == 2) {
            $query->whereBetween(
                'updated_at',
                [$params['updated_at'][0], $params['updated_at'][1]]
            );
        }

        if (isset($params['deleted_at']) && filled($params['deleted_at']) && is_array($params['deleted_at']) && count($params['deleted_at']) == 2) {
            $query->whereBetween(
                'deleted_at',
                [$params['deleted_at'][0], $params['deleted_at'][1]]
            );
        }

        return $query;
    }

    /**
     * Get node group by client ID.
     */
    public function getNodeGroupByClientID(string $clientID): ?array
    {
        $query = $this->model::query();

        $result = $query->where(['enable' => 1, 'client_id' => $clientID])->first();

        return $result ? $result->toArray() : null;
    }
}

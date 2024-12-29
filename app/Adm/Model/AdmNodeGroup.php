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

namespace App\Adm\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\SoftDeletes;
use Mine\MineCollection;
use Mine\MineModel;

/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property int $enable
 * @property int $weight
 * @property string $client_id
 * @property string $client_secret
 * @property array $ext
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property null|AdmNode[]|MineCollection $Node
 */
class AdmNodeGroup extends MineModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'adm_node_group';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'name_en', 'enable', 'weight', 'client_id', 'client_secret', 'ext', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'enable' => 'integer', 'weight' => 'integer', 'ext' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * Creating event.
     */
    public function creating(Creating $event)
    {
        $this->client_id = substr(uuid(), 0, 8);
        $this->client_secret = substr(str_replace('-', '', uuid()), 0, 16);
    }

    /**
     * Defining Node related table.
     */
    public function Node()
    {
        return $this->hasMany(AdmNode::class, 'adm_node_group_id', 'id');
    }
}

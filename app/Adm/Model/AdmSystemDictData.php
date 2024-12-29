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
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;
use Mine\MineModel;

/**
 * @property int $id primary key
 * @property int $type_id dictionary type ID
 * @property string $label dictionary label
 * @property string $value dictionary value
 * @property string $code dictionary mark
 * @property int $sort sort
 * @property int $status status (1 normal 2 disabled)
 * @property int $created_by creator
 * @property int $updated_by updater
 * @property Carbon $created_at creation time
 * @property Carbon $updated_at update time
 * @property string $deleted_at deletion time
 * @property string $remark remarks
 */
class AdmSystemDictData extends MineModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'system_dict_data';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'type_id', 'label', 'label_en', 'value', 'code', 'sort', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'type_id' => 'integer', 'sort' => 'integer', 'status' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * Defining Node related table.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(AdmNode::class, 'isp', 'value');
    }
}

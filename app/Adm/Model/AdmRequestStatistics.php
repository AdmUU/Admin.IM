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
use Mine\MineModel;

/**
 * @property int $id
 * @property string $optype
 * @property Carbon $date
 * @property int $year
 * @property int $month
 * @property int $day
 * @property int $request_count
 */
class AdmRequestStatistics extends MineModel
{
    public bool $timestamps = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'adm_request_statistics';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['optype', 'date', 'year', 'month', 'day', 'request_count'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'optype' => 'string', 'date' => 'date:Y-m-d', 'year' => 'integer', 'month' => 'integer', 'day' => 'integer', 'request_count' => 'integer'];
}

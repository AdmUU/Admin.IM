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
use Hyperf\Database\Model\Relations\belongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Mine\MineModel;

/**
 * @property int $id ID
 * @property mixed $did distributed ID
 * @property int $adm_node_group_id
 * @property string $name node name
 * @property string $name_en english name
 * @property int $enable 0:disable,1:enabled
 * @property int $block 0:normal,1:blocked
 * @property mixed $ipv4 IPv4
 * @property mixed $ipv6 IPv6
 * @property string $country country code
 * @property string $province province
 * @property string $region region code
 * @property string $continent continent code
 * @property null|AdmSystemDictData $isp telecom operator
 * @property int $online_status 0:offline,1:online
 * @property int $online_total_time online time
 * @property string $online_last_time last online time
 * @property int $weight order weight
 * @property string $version version number
 * @property int $connection_type 1:own,2:anonymous,3:user
 * @property int $sponsor_id sponsor ID
 * @property string $sponsor_name sponsor name
 * @property string $sponsor_url sponsor url
 * @property string $sponsor_status review|approval
 * @property string $fingerprint fingerprint
 * @property string $auth_code authorization code
 * @property array $ext extended info
 * @property int $created_by creator
 * @property int $updated_by updater
 * @property Carbon $created_at Creation time
 * @property Carbon $updated_at Update time
 * @property string $deleted_at Delete time
 * @property string $remark comment
 */
class AdmNode extends MineModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'adm_node';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'did', 'adm_node_group_id', 'name', 'name_en', 'enable', 'block', 'ipv4', 'ipv6', 'country', 'province', 'region', 'continent', 'isp', 'online_status', 'online_total_time', 'online_last_time', 'weight', 'version', 'connection_type', 'sponsor_id', 'sponsor_name', 'sponsor_url', 'sponsor_status', 'fingerprint', 'auth_code', 'ext', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'did' => 'integer', 'adm_node_group_id' => 'integer', 'enable' => 'integer', 'block' => 'integer', 'ipv4' => 'integer', 'online_status' => 'integer', 'online_total_time' => 'integer', 'weight' => 'integer', 'version' => 'string', 'connection_type' => 'integer', 'sponsor_id' => 'integer', 'sponsor_name' => 'string', 'sponsor_url' => 'string', 'sponsor_status' => 'string', 'auth_code' => 'string', 'ext' => 'array', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * Creating event.
     */
    public function creating(Creating $event)
    {
        if (! isset($this->did)) {
            $this->did = snowflake_id();
        }
    }

    /**
     * Set attribute.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'did' && $this->exists) {
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Set ipv4 attribute.
     *
     * @param mixed $value
     */
    public function setIpv4Attribute($value)
    {
        $this->attributes['ipv4'] = ip2long($value) ?: null;
    }

    /**
     * Get ipv4 attribute.
     *
     * @param mixed $value
     */
    public function getIpv4Attribute($value)
    {
        return is_null($value) ? null : long2ip($value);
    }

    /**
     * Set ipv6 attribute.
     *
     * @param mixed $value
     */
    public function setIpv6Attribute($value)
    {
        $this->attributes['ipv6'] = inet_pton($value) ?: null;
    }

    /**
     * Get ipv6 attribute.
     *
     * @param mixed $value
     */
    public function getIpv6Attribute($value)
    {
        return is_null($value) ? null : inet_ntop($value);
    }

    /**
     * Defining ISP related table.
     *
     * @return belongsTo
     */
    public function isp()
    {
        return $this->belongsTo(AdmSystemDictData::class, 'isp', 'value')->where('type_id', 15);
    }

    /**
     * Defining Group related table.
     *
     * @return belongsTo
     */
    public function Group()
    {
        return $this->belongsTo(AdmNodeGroup::class, 'adm_node_group_id', 'id');
    }
}

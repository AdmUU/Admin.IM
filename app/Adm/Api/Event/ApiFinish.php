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

namespace App\Adm\Api\Event;

use Psr\Http\Message\ResponseInterface;

/**
 * Event for ApiFinish.
 */
class ApiFinish
{
    protected ?array $apiData;

    protected ResponseInterface $result;

    public function __construct(?array $apiData, ResponseInterface $result)
    {
        $this->apiData = $apiData;
        $this->result = $result;
    }

    /**
     * Get API data.
     */
    public function getApiData(): ?array
    {
        return $this->apiData;
    }

    /**
     * Get API result.
     */
    public function getResult(): ResponseInterface
    {
        return $this->result;
    }
}

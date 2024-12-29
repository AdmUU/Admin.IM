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

namespace App\Adm\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * API rate limit.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class ApiLimit extends AbstractAnnotation
{
    /**
     * @param null|string $message prompt message
     * @param null|int $num10 limit request numbers (10 seconds), default is 10
     * @param null|int $num60 limit request numbers (60 seconds), default is 30
     */
    public function __construct(public ?string $message = null, public ?int $num10 = 10, public ?int $num60 = 30) {}
}

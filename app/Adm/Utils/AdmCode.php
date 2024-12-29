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

namespace App\Adm\Utils;

class AdmCode
{
    public const API_NEED_CAPTCHA = 20010;

    public const API_AUTH_IP_FAILD = 20011;

    public const API_AUTH_CLIENT_ID_FAILD = 20012;

    public const API_AUTH_CODE_NOT_FOUND = 20013;

    public const API_AUTH_CODE_DISABLED = 20014;

    public const API_AUTH_CODE_BLOCKED = 20015;

    public const API_AUTH_TIMESTAMP_WRONG = 20016;

    public const API_AUTH_NONCE_DUPLICATE = 20017;

    public const IP_REGIST_FAILD = 20051;

    public const TOO_MANY_REGIST = 20052;

    public const TOO_MANY_REQUEST = 20053;

    public const SOCKET_ALREADY_CONNECTED = 20054;

    public const DATABASE_FAILD = 20101;
}

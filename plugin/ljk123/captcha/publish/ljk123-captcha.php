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
return [
    // 一个验证码有效验证时间(秒)
    'ttl' => 60,
    // 一个验证码可重复尝试次数
    'attempts' => 2,
    // 验证码cacheable前缀
    'cahce_prefix' => 'ljk123',

    // 是否关闭默认登录页面的验证(一般配合前端替换登录页配置使用)
    'close_sys_login_verify' => env('CLOSE_SYS_LOGIN_VERIFY', false),

    // 旋转验证码
    // 验证码图片文件夹 不配置表示插件内默认文件夹 暂时只筛选出png
    // "angle_pool_dir" => 'xxxx path',
    // 从pool中取图片时 文件最大限制
    'angle_image_size_limit' => 1024 * 1024 * 2,
    // 允许的旋转误差角度值  角度制
    'angle_error_offset' => 5,
];

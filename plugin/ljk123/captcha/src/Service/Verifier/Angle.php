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

namespace Plugin\Captcha\Service\Verifier;

class Angle extends AbstractVerifier
{
    private int $allowableErrorOffset;

    public function __construct()
    {
        $this->poolDir = (string) config('ljk123-captcha.angle_pool_dir', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Asset' . DIRECTORY_SEPARATOR . 'Angle' . DIRECTORY_SEPARATOR . 'Pool');
        $this->imageSizeLimit = (int) config('ljk123-captcha.angle_image_size_limit', 1024 * 1024 * 2);
        $this->allowableErrorOffset = (int) config('ljk123-captcha.angle_error_offset', 5); // 允许的旋转误差角度值  角度制
    }

    public function create()
    {
        $angle = mt_rand(90, 270);

        [$srcImage, $fileExtension] = $this->loadRandFromPool();
        // 获取原图尺寸
        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);
        // 旋转图像
        $rotatedImage = imagerotate($srcImage, $angle, 0);
        imagesavealpha($rotatedImage, true);

        // 计算裁剪掉旋转之后的空白
        // 减去的距离
        $rad = deg2rad($angle);
        $width = min($srcWidth, $srcHeight);
        $tan = abs(tan($rad));
        $crop = min(abs(sin($rad) * $width), abs(cos($rad) * $width));
        $square = sqrt(pow(1 / (1 + $tan), 2) + pow($tan / (1 + $tan), 2)) * $width;
        // 旋转后的高宽
        $rotatedWidth = imagesx($rotatedImage);
        $rotatedHeight = imagesy($rotatedImage);
        // 最大正方形边长
        $cropX = ($rotatedWidth - $square) / 2;
        $cropY = ($rotatedHeight - $square) / 2;
        // 裁剪图像
        $croppedImage = imagecrop($rotatedImage, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $square,
            'height' => $square,
        ]);

        // 返回 Base64 编码的图像内容
        $imgdata = self::gdImage2Base64($croppedImage);

        // 清理内存
        imagedestroy($srcImage);
        imagedestroy($rotatedImage);
        imagedestroy($croppedImage);

        $server_option = [
            'angle' => $angle,
        ];
        $client_option = [
            'type' => 0,
            'img' => $imgdata,
        ];
        return compact('server_option', 'client_option');
    }

    public function verify(array $server_option, array $verify): bool
    {
        if (! isset($verify['angle'])) {
            return false;
        }
        // TODO:验证轨迹时间
        if (abs($server_option['angle'] - $verify['angle']) < $this->allowableErrorOffset) {
            return true;
        }
        return false;
    }
}

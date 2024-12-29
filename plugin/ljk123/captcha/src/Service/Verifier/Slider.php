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

class Slider extends AbstractVerifier
{
    protected string $poolDir;

    protected int $imageSizeLimit;

    private int $allowableErrorOffset;

    private string $sliderPath;

    public function __construct()
    {
        $this->poolDir = (string) config('ljk123-captcha.slider_pool_dir', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Asset' . DIRECTORY_SEPARATOR . 'Slider' . DIRECTORY_SEPARATOR . 'Pool');
        $this->sliderPath = (string) config('ljk123-captcha.slider_path', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Asset' . DIRECTORY_SEPARATOR . 'Slider' . DIRECTORY_SEPARATOR . 'slider.png');
        $this->imageSizeLimit = (int) config('ljk123-captcha.slider_image_size_limit', 1024 * 1024 * 2);
        $this->allowableErrorOffset = (int) config('ljk123-captcha.slider_error_offset', 1); // 允许的滑块偏移量 百分比
    }

    public function create()
    {
        if (! is_file($this->sliderPath)) {
            throw new \RuntimeException('slider_path不存在:' . $this->sliderPath);
        }
        // 遮盖层
        [$width_z, $height_z, $type_z, $attr_z] = getimagesize($this->sliderPath);
        if ($width_z !== $height_z) {
            throw new \RuntimeException('slider_path需是正方形:' . $this->sliderPath);
        }
        [$srcImage, $fileExtension] = $this->loadRandFromPool();

        $srcImage = self::toJpg($srcImage);
        $slider = self::loadImg($this->sliderPath);
        // 获取原图尺寸
        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);

        // 缺口不出现在边上
        $border = 10;
        $width_max = $srcWidth - $width_z - $border;
        $height_max = $srcHeight - $height_z - $border;

        $width_ini = mt_rand($width_z + $border, $width_max);
        $height_ini = mt_rand($border, $height_max);

        // 是否旋转90度
        $isRotate = mt_rand(0, 1) === 1;

        // 生成高度一致的透明图
        $sliderBgImage = self::createTransparent($width_z, $srcHeight);
        for ($i = 0; $i < $width_z; ++$i) {
            for ($j = 0; $j < $height_z; ++$j) {
                $hasColor = imagecolorat($slider, $i, $j);
                if ($hasColor > 0) {
                    [$putx, $puty] = $isRotate ? [$i, $j] : [$j, $i];
                    $rgbColor = imagecolorat($srcImage, $putx + $width_ini, $puty + $height_ini);
                    imagesetpixel($sliderBgImage, $putx, $puty + $height_ini, $rgbColor); // 填充
                    $r = ($hasColor >> 16) & 0xFF;
                    $g = ($hasColor >> 8) & 0xFF;
                    $b = $hasColor & 0xFF;
                    imagesetpixel($srcImage, $putx + $width_ini, $puty + $height_ini, imagecolorexactalpha($srcImage, $r, $g, $b, 50)); // 背景扣一下
                }
            }
        }

        imagedestroy($slider);

        $widthX = $width_ini / $srcWidth * 100;

        $bgdata = self::gdImage2Base64($srcImage);
        $sliderdata = self::gdImage2Base64($sliderBgImage);

        // 清理内存
        imagedestroy($srcImage);
        imagedestroy($sliderBgImage);

        $server_option = [
            'x' => $widthX,
        ];
        $client_option = [
            'type' => 1,
            'bg' => $bgdata,
            'slider' => $sliderdata,
        ];
        return compact('server_option', 'client_option');
    }

    public function verify(array $server_option, array $verify): bool
    {
        if (! isset($verify['x'])) {
            return false;
        }
        // TODO:验证轨迹时间
        if (abs($server_option['x'] - $verify['x']) < $this->allowableErrorOffset) {
            return true;
        }
        return false;
    }
}

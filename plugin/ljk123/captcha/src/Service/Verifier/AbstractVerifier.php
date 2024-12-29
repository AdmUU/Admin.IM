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

abstract class AbstractVerifier
{
    protected string $poolDir;

    protected array $poolImgs;

    protected int $imageSizeLimit = 1024 * 1024 * 2;

    protected static function loadImg(string $filePath, &$fileExtension = null): \GdImage
    {
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($fileExtension) {
            case 'jpg':
            case 'jpeg':
                $srcImage = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $srcImage = imagecreatefrompng($filePath);
                break;
            case 'webp':
                $srcImage = imagecreatefromwebp($filePath);
                break;
            case 'bmp':
                $srcImage = imagecreatefrombmp($filePath);
                break;
            default:
                throw new \RuntimeException('Unsupported image format!');
        }

        if (! $srcImage) {
            throw new \RuntimeException('Failed to load image!');
        }
        return $srcImage;
    }

    protected static function saveImg(\GdImage $srcImage, string $tempFile)
    {
        $fileExtension = strtolower(pathinfo($tempFile, PATHINFO_EXTENSION));
        // 输出裁剪后的图像到临时文件
        switch ($fileExtension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($srcImage, $tempFile);
                break;
            case 'png':
                return imagepng($srcImage, $tempFile);
                break;
            case 'webp':
                return imagewebp($srcImage, $tempFile);
                break;
            case 'bmp':
                return imagebmp($srcImage, $tempFile);
                break;
        }
        return false;
    }

    protected static function toJpg(\GdImage $srcImage): false|\GdImage
    {
        $tempFile = tempnam(sys_get_temp_dir(), '__toJpg') . '.jpg';
        imagejpeg($srcImage, $tempFile);
        imagedestroy($srcImage);
        return imagecreatefromjpeg($tempFile);
    }

    protected static function createTransparent(int $width, int $height): false|\GdImage
    {
        if (! $image = imagecreatetruecolor($width, $height)) {
            return false;
        }
        try {
            if (imagesavealpha($image, true) === false) {
                throw new \RuntimeException('imagesavealpha fail');
            }
            if (false === $transColor = imagecolorallocatealpha($image, 255, 0, 0, 127)) {
                throw new \RuntimeException('imagecolorallocatealpha fail');
            }
            if (false === $transColor = imagefill($image, 0, 0, $transColor)) {
                throw new \RuntimeException('imagefill fail');
            }
        } catch (\RuntimeException $e) {
            imagedestroy($image);
            return false;
        }
        return $image;
    }

    /**
     * Undocumented function.
     *
     * @return array{0: \GdImage, 1: string}
     */
    protected function loadRandFromPool(): array
    {
        if (empty($this->poolImgs)) {
            if (! is_dir($this->poolDir)) {
                throw new \RuntimeException('pool_dir目录不存在:' . $this->poolDir);
            }
            // 获取文件夹中的所有文件
            $items = new \FilesystemIterator($this->poolDir);
            $files = [];
            foreach ($items as $item) {
                // 如果是文件则删除文件
                if ($item->isFile()) {
                    $files[] = $item->getPathname();
                }
            }
            // 过滤出图片文件 暂时只要png
            $allowedExtensions = ['png'];
            $imageFiles = array_values(array_filter($files, function ($file) use ($allowedExtensions) {
                $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (! in_array($fileExtension, $allowedExtensions)) {
                    console()->warning("Unsupported format {$fileExtension},Ignored");
                    return false;
                }
                if (filesize($file) > $this->imageSizeLimit) {
                    console()->warning("Pool image {$file} size is too large,Ignored");
                    return false;
                }
                return true;
            }));

            if (empty($imageFiles)) {
                throw new \RuntimeException('No image files found in directory!');
            }
            $this->poolImgs = $imageFiles;
        }
        // 随机选择一张图片
        $filePath = $this->poolImgs[array_rand($this->poolImgs)];
        $image = self::loadImg($filePath, $fileExtension);
        return [$image, $fileExtension];
    }

    protected static function gdImage2Base64(\GdImage $image, string $ext = 'png')
    {
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bwp'])) {
            $ext = 'png';
        }
        $tempFile = tempnam(sys_get_temp_dir(), '__gdImage2Base64') . '.' . $ext;
        if (self::saveImg($image, $tempFile) === false) {
            return false;
        }
        $base64Image = base64_encode(file_get_contents($tempFile));
        // 删除临时文件
        unlink($tempFile);
        return 'data:image/jpg;base64,' . $base64Image;
    }
}

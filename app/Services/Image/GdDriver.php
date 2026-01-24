<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Services\TextLayout;
use RuntimeException;

final class GdDriver
{
    public function __construct(private TextLayout $layout)
    {
    }

    public function render(array $template, array $fields, string $inputPath, string $outputPath, string $previewPath, string $headline, string $subhead, ?string $overlayPath): void
    {
        $image = imagecreatetruecolor((int)$template['width'], (int)$template['height']);
        [$r, $g, $b] = $this->hexToRgb($template['background_color'] ?: '#000000');
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        $photo = $this->loadImage($inputPath);
        $targetW = (int)$template['width'];
        $targetH = (int)$template['height'];
        if ($template['media_fit_mode'] === 'contain') {
            [$newW, $newH] = $this->containSize(imagesx($photo), imagesy($photo), $targetW, $targetH);
            $x = (int)(($targetW - $newW) / 2);
            $y = (int)(($targetH - $newH) / 2);
            imagecopyresampled($image, $photo, $x, $y, 0, 0, $newW, $newH, imagesx($photo), imagesy($photo));
        } else {
            [$cropW, $cropH, $srcX, $srcY] = $this->coverCrop(imagesx($photo), imagesy($photo), $targetW, $targetH);
            imagecopyresampled($image, $photo, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);
        }

        foreach ($fields as $field) {
            $text = $field['field_key'] === 'headline' ? $headline : $subhead;
            if ($text === '') {
                continue;
            }
            if (empty($field['font_path']) || !file_exists($field['font_path'])) {
                continue;
            }
            $measure = function (string $string, int $fontSize) use ($field): float {
                $bbox = imagettfbbox($fontSize, 0, $field['font_path'], $string);
                return (float)($bbox[2] - $bbox[0]);
            };

            $boxW = (int)$field['w'] - 2 * (int)$field['padding'];
            $boxH = (int)$field['h'] - 2 * (int)$field['padding'];
            $layout = $this->layout->fit(
                $text,
                $measure,
                $boxW,
                $boxH,
                (int)$field['base_font_size'],
                (int)$field['min_font_size'],
                (int)$field['max_lines'],
                (float)$field['line_height']
            );
            $this->drawText($image, $layout, $field);
        }

        if ($overlayPath && file_exists($overlayPath)) {
            $overlay = imagecreatefrompng($overlayPath);
            imagecopy($image, $overlay, 0, 0, 0, 0, imagesx($overlay), imagesy($overlay));
            imagedestroy($overlay);
        }

        imagejpeg($image, $previewPath, 85);
        if ($template['export_format'] === 'png') {
            imagepng($image, $outputPath);
        } else {
            imagejpeg($image, $outputPath, 92);
        }

        imagedestroy($image);
        imagedestroy($photo);
    }

    private function drawText($image, array $layout, array $field): void
    {
        $fontSize = $layout['fontSize'];
        $lines = $layout['lines'];
        $lineHeight = $fontSize * (float)$field['line_height'];
        $boxX = (int)$field['x'] + (int)$field['padding'];
        $boxY = (int)$field['y'] + (int)$field['padding'];
        $boxW = (int)$field['w'] - 2 * (int)$field['padding'];
        $boxH = (int)$field['h'] - 2 * (int)$field['padding'];
        $blockHeight = count($lines) * $lineHeight;

        $y = $boxY;
        if ($field['valign'] === 'middle') {
            $y = $boxY + ($boxH - $blockHeight) / 2;
        } elseif ($field['valign'] === 'bottom') {
            $y = $boxY + ($boxH - $blockHeight);
        }

        [$r, $g, $b] = $this->hexToRgb($field['color']);
        $color = imagecolorallocate($image, $r, $g, $b);
        $strokeColor = imagecolorallocate($image, ...$this->hexToRgb($field['stroke_color']));
        $shadowColor = imagecolorallocate($image, ...$this->hexToRgb($field['shadow_color']));

        foreach ($lines as $line) {
            $bbox = imagettfbbox($fontSize, 0, $field['font_path'], $line);
            $textW = $bbox[2] - $bbox[0];
            $x = $boxX;
            if ($field['align'] === 'center') {
                $x = $boxX + ($boxW - $textW) / 2;
            } elseif ($field['align'] === 'right') {
                $x = $boxX + ($boxW - $textW);
            }
            if (!empty($field['shadow_enabled'])) {
                imagettftext($image, $fontSize, 0, $x + (int)$field['shadow_x'], $y + (int)$field['shadow_y'] + $fontSize, $shadowColor, $field['font_path'], $line);
            }
            if (!empty($field['stroke_enabled'])) {
                $strokeWidth = (int)$field['stroke_width'];
                for ($sx = -$strokeWidth; $sx <= $strokeWidth; $sx++) {
                    for ($sy = -$strokeWidth; $sy <= $strokeWidth; $sy++) {
                        imagettftext($image, $fontSize, 0, $x + $sx, $y + $sy + $fontSize, $strokeColor, $field['font_path'], $line);
                    }
                }
            }
            imagettftext($image, $fontSize, 0, $x, $y + $fontSize, $color, $field['font_path'], $line);
            $y += $lineHeight;
        }
    }

    private function loadImage(string $path)
    {
        $info = getimagesize($path);
        return match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => throw new RuntimeException('Unsupported image format'),
        };
    }

    private function containSize(int $srcW, int $srcH, int $dstW, int $dstH): array
    {
        $ratio = min($dstW / $srcW, $dstH / $srcH);
        return [(int)round($srcW * $ratio), (int)round($srcH * $ratio)];
    }

    private function coverCrop(int $srcW, int $srcH, int $dstW, int $dstH): array
    {
        $ratio = max($dstW / $srcW, $dstH / $srcH);
        $cropW = (int)round($dstW / $ratio);
        $cropH = (int)round($dstH / $ratio);
        $srcX = (int)(($srcW - $cropW) / 2);
        $srcY = (int)(($srcH - $cropH) / 2);
        return [$cropW, $cropH, $srcX, $srcY];
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $int = hexdec($hex);
        return [($int >> 16) & 255, ($int >> 8) & 255, $int & 255];
    }
}

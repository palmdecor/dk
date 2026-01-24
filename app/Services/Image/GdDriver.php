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

    public function render(array $template, array $fields, string $inputPath, string $outputPath, string $previewPath, string $headline, string $subhead, ?string $overlayPath, array $transform): void
    {
        if (!function_exists('imagettftext')) {
            throw new RuntimeException('GD FreeType desteği yok.');
        }
        $image = imagecreatetruecolor((int)$template['width'], (int)$template['height']);
        [$r, $g, $b] = $this->hexToRgb($template['background_color'] ?: '#000000');
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        $photo = $this->loadImage($inputPath);
        $targetW = (int)$template['width'];
        $targetH = (int)$template['height'];
        $zoom = max(1.0, min(2.0, (float)($transform['zoom'] ?? 1.0)));
        $offsetX = (int)($transform['offset_x'] ?? 0);
        $offsetY = (int)($transform['offset_y'] ?? 0);

        $srcW = imagesx($photo);
        $srcH = imagesy($photo);
        $baseScale = $template['media_fit_mode'] === 'contain' ? min($targetW / $srcW, $targetH / $srcH) : max($targetW / $srcW, $targetH / $srcH);
        $scale = $baseScale * $zoom;
        $newW = (int)round($srcW * $scale);
        $newH = (int)round($srcH * $scale);

        $x = (int)(($targetW - $newW) / 2) + $offsetX;
        $y = (int)(($targetH - $newH) / 2) + $offsetY;
        imagecopyresampled($image, $photo, $x, $y, 0, 0, $newW, $newH, $srcW, $srcH);

        foreach ($fields as $field) {
            $text = $field['field_key'] === 'headline' ? $headline : $subhead;
            if ($text === '') {
                continue;
            }
            if (empty($field['font_path']) || !file_exists($field['font_path'])) {
                throw new RuntimeException('Font dosyası bulunamadı: ' . $field['field_key']);
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

        imagejpeg($image, $previewPath, 95);
        if ($template['export_format'] === 'png') {
            imagepng($image, $outputPath);
        } else {
            imagejpeg($image, $outputPath, 95);
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
            default => throw new RuntimeException('Desteklenmeyen görsel formatı'),
        };
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

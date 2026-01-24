<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Services\TextLayout;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use RuntimeException;

final class ImagickDriver
{
    public function __construct(private TextLayout $layout)
    {
    }

    public function render(array $template, array $fields, string $inputPath, string $outputPath, string $previewPath, string $headline, string $subhead, ?string $overlayPath): void
    {
        $canvas = new Imagick();
        $canvas->newImage((int)$template['width'], (int)$template['height'], new ImagickPixel($template['background_color'] ?: '#000000'));
        $canvas->setImageFormat('png');

        $photo = new Imagick($inputPath);
        $photo->setImageColorspace(Imagick::COLORSPACE_SRGB);
        $photo->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        $targetW = (int)$template['width'];
        $targetH = (int)$template['height'];
        if ($template['media_fit_mode'] === 'contain') {
            $photo->thumbnailImage($targetW, $targetH, true);
            $x = (int)(($targetW - $photo->getImageWidth()) / 2);
            $y = (int)(($targetH - $photo->getImageHeight()) / 2);
            $canvas->compositeImage($photo, Imagick::COMPOSITE_OVER, $x, $y);
        } else {
            $photo->cropThumbnailImage($targetW, $targetH);
            $canvas->compositeImage($photo, Imagick::COMPOSITE_OVER, 0, 0);
        }

        foreach ($fields as $field) {
            $text = $field['field_key'] === 'headline' ? $headline : $subhead;
            if ($text === '') {
                continue;
            }
            if (empty($field['font_path']) || !file_exists($field['font_path'])) {
                throw new RuntimeException('Font file missing for ' . $field['field_key']);
            }
            $measure = function (string $string, int $fontSize) use ($canvas, $field): float {
                $draw = new ImagickDraw();
                $draw->setFont($field['font_path']);
                $draw->setFontSize($fontSize);
                $draw->setTextEncoding('UTF-8');
                $metrics = $canvas->queryFontMetrics($draw, $string);
                return (float)$metrics['textWidth'];
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

            $this->drawText($canvas, $layout, $field);
        }

        if ($overlayPath && file_exists($overlayPath)) {
            $overlay = new Imagick($overlayPath);
            $canvas->compositeImage($overlay, Imagick::COMPOSITE_OVER, 0, 0);
        }

        $preview = clone $canvas;
        $preview->setImageFormat('jpeg');
        $preview->setImageCompressionQuality(85);
        $preview->writeImage($previewPath);

        $canvas->setImageFormat($template['export_format']);
        $canvas->writeImage($outputPath);
    }

    private function drawText(Imagick $canvas, array $layout, array $field): void
    {
        $draw = new ImagickDraw();
        $draw->setFont($field['font_path']);
        $draw->setFontSize($layout['fontSize']);
        $draw->setFillColor(new ImagickPixel($field['color']));
        $draw->setTextEncoding('UTF-8');
        $lineHeight = $layout['fontSize'] * (float)$field['line_height'];
        $lines = $layout['lines'];

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

        foreach ($lines as $line) {
            $metrics = $canvas->queryFontMetrics($draw, $line);
            $x = $boxX;
            if ($field['align'] === 'center') {
                $x = $boxX + ($boxW - $metrics['textWidth']) / 2;
            } elseif ($field['align'] === 'right') {
                $x = $boxX + ($boxW - $metrics['textWidth']);
            }
            if (!empty($field['shadow_enabled'])) {
                $shadow = clone $draw;
                $shadow->setFillColor(new ImagickPixel($field['shadow_color']));
                $canvas->annotateImage(
                    $shadow,
                    $x + (int)$field['shadow_x'],
                    $y + (int)$field['shadow_y'] + $layout['fontSize'],
                    0,
                    $line
                );
            }
            if (!empty($field['stroke_enabled'])) {
                $stroke = clone $draw;
                $stroke->setFillColor(new ImagickPixel('transparent'));
                $stroke->setStrokeColor(new ImagickPixel($field['stroke_color']));
                $stroke->setStrokeWidth((int)$field['stroke_width']);
                $canvas->annotateImage($stroke, $x, $y + $layout['fontSize'], 0, $line);
            }
            $canvas->annotateImage($draw, $x, $y + $layout['fontSize'], 0, $line);
            $y += $lineHeight;
        }
    }
}

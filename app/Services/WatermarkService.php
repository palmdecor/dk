<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class WatermarkService
{
    public function imagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    protected function manager(): ImageManager
    {
        return new ImageManager(['driver' => $this->imagickAvailable() ? 'imagick' : 'gd']);
    }

    public function applyWatermark(string $originalPath, Setting $settings): string
    {
        $manager = $this->manager();
        $image = $manager->make(Storage::path($originalPath));
        $watermark = $manager->make(Storage::path($settings->watermark_path));

        $watermark = $this->scaleWatermark($watermark, $image->width(), $settings);
        $watermark->opacity($settings->opacity);

        [$x, $y] = $this->calculatePosition($image->width(), $image->height(), $watermark->width(), $watermark->height(), $settings);
        $image->insert($watermark, 'top-left', $x, $y);

        $outputPath = 'processed/' . uniqid('processed_', true) . '.jpg';
        $image->save(Storage::path($outputPath), $settings->output_quality);

        return $outputPath;
    }

    protected function scaleWatermark($watermark, int $imageWidth, Setting $settings)
    {
        if ($settings->scale_mode === 'percent') {
            $targetWidth = max(1, (int) ($imageWidth * ($settings->scale_value / 100)));
        } else {
            $targetWidth = $settings->scale_value;
        }

        return $watermark->resize($targetWidth, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    protected function calculatePosition(int $imageWidth, int $imageHeight, int $wmWidth, int $wmHeight, Setting $settings): array
    {
        $padding = $settings->padding;
        $positions = [
            'top_left' => [$padding, $padding],
            'top_right' => [$imageWidth - $wmWidth - $padding, $padding],
            'bottom_left' => [$padding, $imageHeight - $wmHeight - $padding],
            'bottom_right' => [$imageWidth - $wmWidth - $padding, $imageHeight - $wmHeight - $padding],
            'center' => [($imageWidth - $wmWidth) / 2, ($imageHeight - $wmHeight) / 2],
        ];

        if ($settings->position_mode === 'custom') {
            return [$settings->custom_x ?? 0, $settings->custom_y ?? 0];
        }

        return $positions[$settings->position_mode] ?? $positions['bottom_right'];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Image\GdDriver;
use App\Services\Image\ImagickDriver;

final class Renderer
{
    public function __construct(private TextLayout $layout)
    {
    }

    public function render(array $template, array $fields, string $inputPath, string $outputPath, string $previewPath, string $headline, string $subhead, ?string $overlayPath): void
    {
        if (extension_loaded('imagick')) {
            $driver = new ImagickDriver($this->layout);
            $driver->render($template, $fields, $inputPath, $outputPath, $previewPath, $headline, $subhead, $overlayPath);
            return;
        }

        $driver = new GdDriver($this->layout);
        $driver->render($template, $fields, $inputPath, $outputPath, $previewPath, $headline, $subhead, $overlayPath);
    }
}

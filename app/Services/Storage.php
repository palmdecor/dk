<?php

declare(strict_types=1);

namespace App\Services;

final class Storage
{
    public function __construct(private array $config)
    {
    }

    public function ensure(): void
    {
        $paths = [
            $this->path(''),
            $this->path('uploads'),
            $this->path('overlays'),
            $this->path('fonts'),
            $this->path('renders'),
            $this->path('previews'),
        ];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }
    }

    public function path(string $key): string
    {
        $root = $this->config['root'];
        $map = $this->config;
        if ($key === '') {
            return $root;
        }
        return $root . '/' . $map[$key];
    }

    public function randomName(string $prefix, string $ext): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    }
}

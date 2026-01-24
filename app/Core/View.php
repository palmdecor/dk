<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private array $globals = [];

    public function __construct(private string $basePath)
    {
    }

    public function share(array $data): void
    {
        $this->globals = array_merge($this->globals, $data);
    }

    public function render(string $view, array $data = []): void
    {
        $path = $this->basePath . '/' . $view . '.php';
        if (!file_exists($path)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            exit;
        }
        $data = array_merge($this->globals, $data);
        extract($data, EXTR_SKIP);
        include $path;
    }
}

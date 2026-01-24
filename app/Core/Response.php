<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function file(string $path, string $downloadName, string $mime): void
    {
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Dosya bulunamadı';
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        readfile($path);
        exit;
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

final class ErrorHandler
{
    public function __construct(private bool $debug)
    {
    }

    public function register(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): void {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (\Throwable $exception): void {
            http_response_code(500);
            if ($this->debug) {
                echo '<h1>Uygulama Hatası</h1>';
                echo '<pre>' . htmlspecialchars((string)$exception, ENT_QUOTES, 'UTF-8') . '</pre>';
                return;
            }
            echo '<h1>Bir hata oluştu</h1><p>Lütfen daha sonra tekrar deneyin.</p>';
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
            return null;
        }
        $message = $_SESSION['flash'][$key] ?? null;
        if ($message !== null) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }

    public function destroy(): void
    {
        session_destroy();
    }
}

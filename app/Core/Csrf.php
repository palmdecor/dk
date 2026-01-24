<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public function __construct(private Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get('csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            $this->session->set('csrf', $token);
        }
        return $token;
    }

    public function field(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($this->token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public function verify(): void
    {
        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($this->session->get('csrf', ''), $token)) {
            http_response_code(400);
            echo 'CSRF hatası';
            exit;
        }
    }
}

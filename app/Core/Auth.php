<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public function __construct(private Session $session)
    {
    }

    public function user(): ?array
    {
        return $this->session->get('user');
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        return ($this->user()['role'] ?? null) === 'admin';
    }

    public function login(array $user): void
    {
        $this->session->set('user', $user);
    }

    public function logout(): void
    {
        $this->session->forget('user');
    }
}

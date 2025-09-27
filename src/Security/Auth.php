<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\CrudRepository;
use JsonException;
use Symfony\Component\HttpFoundation\Session\Session;

final class Auth
{
    private Session $session;
    private CrudRepository $userRepository;

    public function __construct(Session $session, CrudRepository $userRepository)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->findByEmail($email);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        try {
            $permissions = json_decode($user['permissions'] ?? '[]', true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (JsonException) {
            $permissions = [];
        }

        $this->session->set('user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'permissions' => $permissions,
        ]);

        return true;
    }

    public function logout(): void
    {
        $this->session->invalidate();
    }

    public function user(): ?array
    {
        return $this->session->get('user');
    }

    public function checkPermission(string $permission): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        if ($user['role'] === 'admin') {
            return true;
        }

        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions, true);
    }

    private function findByEmail(string $email): ?array
    {
        $stmt = $this->userRepository->connection()->prepare('SELECT * FROM users WHERE email = :email AND status = :status');
        $stmt->execute(['email' => $email, 'status' => 'active']);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}

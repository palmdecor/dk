<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager as SymfonyCsrfTokenManager;

final class CsrfTokenManager
{
    private SymfonyCsrfTokenManager $manager;

    public function __construct()
    {
        $this->manager = new SymfonyCsrfTokenManager();
    }

    public function generateToken(string $id): string
    {
        return $this->manager->getToken($id)->getValue();
    }

    public function isTokenValid(string $id, string $token): bool
    {
        return $this->manager->isTokenValid(new CsrfToken($id, $token));
    }
}

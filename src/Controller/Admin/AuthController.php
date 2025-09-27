<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Security\Auth;
use App\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class AuthController extends BaseController
{
    public function __construct(Environment $twig, Auth $auth, CsrfTokenManager $csrf)
    {
        parent::__construct($twig, $auth, $csrf);
    }

    public function login(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->processLogin($request);
        }

        return $this->render('admin/auth/login.twig', [
            'token_id' => 'auth_login',
        ]);
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();
        return $this->redirect('/admin/login.php');
    }

    private function processLogin(Request $request): void
    {
        $token = $request->request->get('_token');
        if (!$this->csrf->isTokenValid('auth_login', (string) $token)) {
            throw new \RuntimeException('Form güvenlik doğrulaması başarısız.');
        }

        $email = (string) $request->request->get('email');
        $password = (string) $request->request->get('password');

        if (!$this->auth->attempt($email, $password)) {
            throw new \RuntimeException('Geçersiz giriş bilgileri.');
        }
    }
}

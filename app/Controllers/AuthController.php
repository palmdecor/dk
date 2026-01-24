<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\View;
use PDO;

final class AuthController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Csrf $csrf,
        private Response $response,
        private View $view
    ) {
    }

    public function showLogin(): void
    {
        if ($this->auth->check()) {
            $this->response->redirect('/render');
        }
        $this->view->render('auth/login', [
            'csrf' => $this->csrf,
            'error' => null,
        ]);
    }

    public function login(): void
    {
        $this->csrf->verify();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $this->db->pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->auth->login([
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
            ]);
            $this->response->redirect('/render');
        }
        $this->view->render('auth/login', [
            'csrf' => $this->csrf,
            'error' => 'Invalid credentials',
        ]);
    }

    public function logout(): void
    {
        $this->csrf->verify();
        $this->auth->logout();
        $this->response->redirect('/login');
    }
}

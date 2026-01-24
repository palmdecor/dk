<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

final class AdminUsersController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Csrf $csrf,
        private Response $response,
        private View $view,
        private Session $session
    ) {
    }

    private function requireAdmin(): void
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
        }
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            echo 'Yetkisiz';
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->pdo()->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('admin/users', [
            'users' => $users,
            'csrf' => $this->csrf,
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        if ($email === '' || $name === '' || $password === '') {
            $this->session->flash('error', 'Tüm alanları doldurun.');
            $this->response->redirect('/admin/users');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->pdo()->prepare('INSERT INTO users (email, password_hash, name, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$email, $hash, $name, $role, 'active']);
        $this->session->flash('success', 'Üye oluşturuldu.');
        $this->response->redirect('/admin/users');
    }

    public function delete(array $params): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $id = (int)$params['id'];
        if ($id === (int)$this->auth->user()['id']) {
            $this->session->flash('error', 'Kendi hesabınızı silemezsiniz.');
            $this->response->redirect('/admin/users');
        }
        $stmt = $this->db->pdo()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $this->session->flash('success', 'Üye silindi.');
        $this->response->redirect('/admin/users');
    }

    public function toggle(array $params): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT status FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->session->flash('error', 'Üye bulunamadı.');
            $this->response->redirect('/admin/users');
        }
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $update = $this->db->pdo()->prepare('UPDATE users SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $id]);
        $this->session->flash('success', 'Üye durumu güncellendi.');
        $this->response->redirect('/admin/users');
    }
}

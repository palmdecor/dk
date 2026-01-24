<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

final class AdminDashboardController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
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

        $userStats = $this->db->pdo()->query('SELECT users.id, users.name, users.email, COUNT(renders.id) as total FROM users LEFT JOIN renders ON renders.user_id = users.id GROUP BY users.id ORDER BY total DESC')->fetchAll(PDO::FETCH_ASSOC);
        $last7 = $this->db->pdo()->query('SELECT COUNT(*) as total FROM renders WHERE created_at >= (NOW() - INTERVAL 7 DAY)')->fetch(PDO::FETCH_ASSOC);
        $last30 = $this->db->pdo()->query('SELECT COUNT(*) as total FROM renders WHERE created_at >= (NOW() - INTERVAL 30 DAY)')->fetch(PDO::FETCH_ASSOC);
        $avgDuration = $this->db->pdo()->query('SELECT AVG(duration_ms) as avg_ms FROM renders WHERE duration_ms IS NOT NULL')->fetch(PDO::FETCH_ASSOC);

        $recentRenders = $this->db->pdo()->query('SELECT renders.*, users.name as user_name, templates.name as template_name FROM renders LEFT JOIN users ON users.id = renders.user_id LEFT JOIN templates ON templates.id = renders.template_id ORDER BY renders.created_at DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);

        $this->view->render('admin/dashboard', [
            'userStats' => $userStats,
            'last7' => $last7['total'] ?? 0,
            'last30' => $last30['total'] ?? 0,
            'avgDuration' => $avgDuration['avg_ms'] ?? 0,
            'recentRenders' => $recentRenders,
            'csrf' => new \App\Core\Csrf($this->session),
        ]);
    }

    public function deleteRender(array $params): void
    {
        $this->requireAdmin();
        $csrf = new \App\Core\Csrf($this->session);
        $csrf->verify();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE id = ?');
        $stmt->execute([$id]);
        $render = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$render) {
            $this->session->flash('error', 'Render bulunamadı.');
            $this->response->redirect('/admin/dashboard');
        }
        $this->db->pdo()->prepare('DELETE FROM renders WHERE id = ?')->execute([$id]);
        $this->session->flash('success', 'Render silindi.');
        $this->response->redirect('/admin/dashboard');
    }
}

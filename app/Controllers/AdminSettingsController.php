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

final class AdminSettingsController
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
        $settings = $this->fetchSettings();
        $this->view->render('admin/settings', [
            'settings' => $settings,
            'csrf' => $this->csrf,
        ]);
    }

    public function save(): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $renderSeconds = (int)($_POST['render_min_seconds'] ?? 0);
        $telegramUrl = trim($_POST['telegram_url'] ?? '');

        $this->storeSetting('render_min_seconds', (string)$renderSeconds);
        $this->storeSetting('telegram_url', $telegramUrl);

        $this->session->flash('success', 'Ayarlar kaydedildi.');
        $this->response->redirect('/admin/settings');
    }

    private function fetchSettings(): array
    {
        $stmt = $this->db->pdo()->query('SELECT settings_key, settings_value FROM settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['settings_key']] = $row['settings_value'];
        }
        return $settings;
    }

    private function storeSetting(string $key, string $value): void
    {
        $stmt = $this->db->pdo()->prepare('INSERT INTO settings (settings_key, settings_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE settings_value = VALUES(settings_value)');
        $stmt->execute([$key, $value]);
    }
}

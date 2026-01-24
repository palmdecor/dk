<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\View;
use App\Services\Storage;
use PDO;

final class AdminFontsController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Csrf $csrf,
        private Response $response,
        private View $view,
        private Storage $storage,
        private array $config
    ) {
    }

    private function requireAdmin(): void
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
        }
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->pdo()->query('SELECT * FROM fonts ORDER BY created_at DESC');
        $fonts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('admin/fonts/index', [
            'fonts' => $fonts,
            'csrf' => $this->csrf,
        ]);
    }

    public function upload(): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || empty($_FILES['font']['tmp_name'])) {
            http_response_code(400);
            echo 'Missing font';
            exit;
        }
        if ($_FILES['font']['size'] > $this->config['app']['upload_max_size']) {
            http_response_code(400);
            echo 'File too large';
            exit;
        }
        $file = $_FILES['font'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['ttf', 'otf'], true)) {
            http_response_code(400);
            echo 'Invalid font type';
            exit;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ['font/ttf', 'font/otf', 'application/x-font-ttf', 'application/font-sfnt', 'application/octet-stream'], true)) {
            http_response_code(400);
            echo 'Invalid font mime';
            exit;
        }
        $filename = $this->storage->randomName('font', $ext);
        $dest = $this->storage->path('fonts') . '/' . $filename;
        move_uploaded_file($file['tmp_name'], $dest);

        $stmt = $this->db->pdo()->prepare('INSERT INTO fonts (name, file_path, status, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$name, $filename, 'active']);
        $this->response->redirect('/admin/fonts');
    }

    public function toggle(array $params): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT status FROM fonts WHERE id = ?');
        $stmt->execute([$id]);
        $font = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$font) {
            http_response_code(404);
            echo 'Font not found';
            exit;
        }
        $newStatus = $font['status'] === 'active' ? 'inactive' : 'active';
        $update = $this->db->pdo()->prepare('UPDATE fonts SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $id]);
        $this->response->redirect('/admin/fonts');
    }
}

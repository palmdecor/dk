<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\Session;
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
        private Session $session,
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
            'freetype' => function_exists('imagettftext'),
            'previewPath' => $this->storage->path('previews'),
        ]);
    }

    public function upload(): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || empty($_FILES['font']['tmp_name'])) {
            $this->session->flash('error', 'Font name and file are required.');
            $this->response->redirect('/admin/fonts');
        }
        if ($_FILES['font']['size'] > $this->config['app']['upload_max_size']) {
            $this->session->flash('error', 'Font file is too large.');
            $this->response->redirect('/admin/fonts');
        }
        $file = $_FILES['font'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['ttf', 'otf'], true)) {
            $this->session->flash('error', 'Font must be a .ttf or .otf file.');
            $this->response->redirect('/admin/fonts');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = ['font/ttf', 'font/otf', 'application/x-font-ttf', 'application/font-sfnt', 'application/octet-stream'];
        if (!in_array($mime, $allowedMimes, true)) {
            $this->session->flash('error', 'Font mime type is not supported.');
            $this->response->redirect('/admin/fonts');
        }
        $filename = $this->storage->randomName('font', $ext);
        $dest = $this->storage->path('fonts') . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->flash('error', 'Failed to save font file.');
            $this->response->redirect('/admin/fonts');
        }
        chmod($dest, 0664);

        $stmt = $this->db->pdo()->prepare('INSERT INTO fonts (name, file_path, status, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$name, $filename, 'active']);
        $this->session->flash('success', 'Font uploaded successfully.');
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
            $this->session->flash('error', 'Font not found.');
            $this->response->redirect('/admin/fonts');
        }
        $newStatus = $font['status'] === 'active' ? 'inactive' : 'active';
        $update = $this->db->pdo()->prepare('UPDATE fonts SET status = ? WHERE id = ?');
        $update->execute([$newStatus, $id]);
        $this->session->flash('success', 'Font status updated.');
        $this->response->redirect('/admin/fonts');
    }

    public function test(array $params): void
    {
        $this->requireAdmin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM fonts WHERE id = ?');
        $stmt->execute([$id]);
        $font = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$font) {
            $this->session->flash('error', 'Font not found.');
            $this->response->redirect('/admin/fonts');
        }
        $fontPath = $this->storage->path('fonts') . '/' . $font['file_path'];
        if (!file_exists($fontPath)) {
            $this->session->flash('error', 'Font file missing.');
            $this->response->redirect('/admin/fonts');
        }
        $previewPath = $this->storage->path('previews') . '/font_' . $id . '.png';

        try {
            if (extension_loaded('imagick')) {
                $image = new \Imagick();
                $image->newImage(600, 160, new \ImagickPixel('#1f1f1f'));
                $image->setImageFormat('png');
                $draw = new \ImagickDraw();
                $draw->setFont($fontPath);
                $draw->setFontSize(36);
                $draw->setFillColor(new \ImagickPixel('#ffffff'));
                $draw->setTextEncoding('UTF-8');
                $image->annotateImage($draw, 20, 70, 0, 'Font Test');
                $image->annotateImage($draw, 20, 120, 0, $font['name']);
                $image->writeImage($previewPath);
            } else {
                if (!function_exists('imagettftext')) {
                    throw new \RuntimeException('FreeType support is not available.');
                }
                $img = imagecreatetruecolor(600, 160);
                $bg = imagecolorallocate($img, 31, 31, 31);
                imagefill($img, 0, 0, $bg);
                $color = imagecolorallocate($img, 255, 255, 255);
                imagettftext($img, 28, 0, 20, 70, $color, $fontPath, 'Font Test');
                imagettftext($img, 20, 0, 20, 120, $color, $fontPath, $font['name']);
                imagepng($img, $previewPath);
                imagedestroy($img);
            }
            $this->session->flash('success', 'Font preview generated.');
        } catch (\Throwable $e) {
            $this->session->flash('error', 'Font preview failed: ' . $e->getMessage());
        }

        $this->response->redirect('/admin/fonts');
    }
}

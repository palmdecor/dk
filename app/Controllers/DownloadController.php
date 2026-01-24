<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Response;
use App\Services\Storage;
use PDO;

final class DownloadController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Response $response,
        private Storage $storage
    ) {
    }

    public function download(array $params): void
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
        }
        $id = $params['id'];
        if (str_starts_with($id, 'preview_')) {
            $file = str_ends_with($id, '.jpg') ? $id : $id . '.jpg';
            $path = $this->storage->path('previews') . '/' . basename($file);
            if (!file_exists($path)) {
                http_response_code(404);
                echo 'Dosya bulunamadı';
                exit;
            }
            header('Content-Type: image/jpeg');
            readfile($path);
            exit;
        }
        if (str_starts_with($id, 'font_')) {
            $file = str_ends_with($id, '.png') ? $id : $id . '.png';
            $path = $this->storage->path('previews') . '/' . basename($file);
            if (!file_exists($path)) {
                http_response_code(404);
                echo 'Dosya bulunamadı';
                exit;
            }
            header('Content-Type: image/png');
            readfile($path);
            exit;
        }
        if (str_starts_with($id, 'overlay_')) {
            $tplId = (int)str_replace('overlay_', '', $id);
            $stmt = $this->db->pdo()->prepare('SELECT overlay_png_path FROM templates WHERE id = ?');
            $stmt->execute([$tplId]);
            $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tpl || !$tpl['overlay_png_path']) {
                http_response_code(404);
                echo 'Dosya bulunamadı';
                exit;
            }
            $path = $this->storage->path('overlays') . '/' . $tpl['overlay_png_path'];
            header('Content-Type: image/png');
            readfile($path);
            exit;
        }
        $renderId = (int)$id;
        $stmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE id = ? AND user_id = ?');
        $stmt->execute([$renderId, $this->auth->user()['id']]);
        $render = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$render || !$render['output_path']) {
            http_response_code(404);
            echo 'Render bulunamadı';
            exit;
        }
        $path = $this->storage->path('renders') . '/' . $render['output_path'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $this->response->file($path, 'render_' . $renderId . '.' . $ext, $mime);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\ImageRenderer;
use App\Services\Storage;
use App\Services\TelegramNotifier;
use PDO;

final class RenderController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Csrf $csrf,
        private Response $response,
        private View $view,
        private Storage $storage,
        private ImageRenderer $renderer,
        private TelegramNotifier $notifier,
        private Session $session,
        private array $config
    ) {
    }

    private function requireLogin(): void
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
        }
    }

    public function form(): void
    {
        $this->requireLogin();
        $templates = $this->db->pdo()->query('SELECT * FROM templates ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        $rendersStmt = $this->db->pdo()->prepare('SELECT renders.*, templates.name as template_name FROM renders LEFT JOIN templates ON templates.id = renders.template_id WHERE renders.user_id = ? ORDER BY renders.created_at DESC LIMIT 20');
        $rendersStmt->execute([$this->auth->user()['id']]);
        $renders = $rendersStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('user/render', [
            'templates' => $templates,
            'renders' => $renders,
            'csrf' => $this->csrf,
            'minSeconds' => $this->getSetting('render_min_seconds'),
        ]);
    }

    public function templateInfo(array $params): void
    {
        $this->requireLogin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT id, name, width, height, media_fit_mode, background_color, export_format FROM templates WHERE id = ?');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$template) {
            $this->response->json(['error' => 'Şablon bulunamadı'], 404);
        }
        $this->response->json(['template' => $template]);
    }

    public function renderImage(): void
    {
        $this->requireLogin();
        $this->csrf->verify();
        $templateId = (int)($_POST['template_id'] ?? 0);
        $headline = trim($_POST['headline'] ?? '');
        $subhead = trim($_POST['subhead'] ?? '');
        $zoom = (float)($_POST['photo_zoom'] ?? 1.0);
        $offsetX = (int)($_POST['photo_offset_x'] ?? 0);
        $offsetY = (int)($_POST['photo_offset_y'] ?? 0);

        if ($templateId <= 0 || empty($_FILES['photo']['tmp_name'])) {
            $this->session->flash('error', 'Şablon ve fotoğraf zorunludur.');
            $this->response->redirect('/render');
        }
        if ($_FILES['photo']['size'] > $this->config['app']['upload_max_size']) {
            $this->session->flash('error', 'Fotoğraf dosyası çok büyük.');
            $this->response->redirect('/render');
        }
        $file = $_FILES['photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->session->flash('error', 'Fotoğraf JPG/PNG/WEBP olmalıdır.');
            $this->response->redirect('/render');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $this->session->flash('error', 'Fotoğraf MIME hatası.');
            $this->response->redirect('/render');
        }

        $imgInfo = getimagesize($file['tmp_name']);
        if ($imgInfo && ($imgInfo[0] * $imgInfo[1] > $this->config['app']['max_image_pixels'])) {
            $this->session->flash('error', 'Fotoğraf çok büyük. Lütfen daha küçük bir görsel yükleyin.');
            $this->response->redirect('/render');
        }

        $stmt = $this->db->pdo()->prepare('SELECT * FROM templates WHERE id = ?');
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$template) {
            $this->session->flash('error', 'Şablon bulunamadı.');
            $this->response->redirect('/render');
        }
        if (empty($template['overlay_png_path'])) {
            $this->session->flash('error', 'Şablon overlay PNG eksik.');
            $this->response->redirect('/render');
        }

        $filename = $this->storage->randomName('input', $ext);
        $dest = $this->storage->path('uploads') . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->flash('error', 'Fotoğraf kaydedilemedi.');
            $this->response->redirect('/render');
        }

        $fieldsStmt = $this->db->pdo()->prepare('SELECT f.*, fonts.file_path FROM template_text_fields f LEFT JOIN fonts ON fonts.id = f.font_id WHERE f.template_id = ? ORDER BY draw_order');
        $fieldsStmt->execute([$templateId]);
        $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fields as &$field) {
            $field['font_path'] = $field['file_path'] ? $this->storage->path('fonts') . '/' . $field['file_path'] : null;
        }

        $insert = $this->db->pdo()->prepare('INSERT INTO renders (user_id, template_id, input_path, status, params_json, created_at, started_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
        $params = json_encode([
            'headline' => $headline,
            'subhead' => $subhead,
            'offset_x' => $offsetX,
            'offset_y' => $offsetY,
            'zoom' => $zoom,
        ], JSON_UNESCAPED_UNICODE);
        $insert->execute([$this->auth->user()['id'], $templateId, $filename, 'processing', $params]);
        $renderId = (int)$this->db->pdo()->lastInsertId();

        $previewName = $this->storage->randomName('preview', 'jpg');
        $outputName = $this->storage->randomName('render', $template['export_format']);
        $previewPath = $this->storage->path('previews') . '/' . $previewName;
        $outputPath = $this->storage->path('renders') . '/' . $outputName;
        $overlayPath = $this->storage->path('overlays') . '/' . $template['overlay_png_path'];

        $errorMessage = null;
        $start = microtime(true);
        try {
            $this->renderer->render($template, $fields, $dest, $outputPath, $previewPath, $headline, $subhead, $overlayPath, [
                'offset_x' => $offsetX,
                'offset_y' => $offsetY,
                'zoom' => $zoom,
            ]);
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $durationMs = (int)((microtime(true) - $start) * 1000);
        $waitEnabled = (bool)($this->config['app']['render_wait_enabled'] ?? false);
        $minSeconds = $this->getSetting('render_min_seconds');
        if ($waitEnabled && $minSeconds > 0) {
            $minMs = $minSeconds * 1000;
            if ($durationMs < $minMs) {
                usleep(($minMs - $durationMs) * 1000);
                $durationMs = $minMs;
            }
        }

        if ($errorMessage === null) {
            $update = $this->db->pdo()->prepare('UPDATE renders SET output_path = ?, preview_path = ?, status = ?, finished_at = NOW(), duration_ms = ? WHERE id = ?');
            $update->execute([$outputName, $previewName, 'done', $durationMs, $renderId]);
            $this->session->flash('success', 'Render tamamlandı.');
            $this->notifier->notify($this->auth->user()['name'] . ' render tamamladı.');
        } else {
            $update = $this->db->pdo()->prepare('UPDATE renders SET status = ?, error_message = ?, finished_at = NOW(), duration_ms = ? WHERE id = ?');
            $update->execute(['failed', $errorMessage, $durationMs, $renderId]);
            $this->session->flash('error', 'Render başarısız: ' . $errorMessage);
        }

        $this->response->redirect('/render');
    }

    public function status(array $params): void
    {
        $this->requireLogin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->auth->user()['id']]);
        $render = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$render) {
            $this->response->json(['error' => 'Render bulunamadı'], 404);
        }
        $this->response->json($render);
    }

    private function getSetting(string $key): int
    {
        $stmt = $this->db->pdo()->prepare('SELECT settings_value FROM settings WHERE settings_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['settings_value'] : 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\Renderer;
use App\Services\Storage;
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
        private Renderer $renderer,
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

    public function templates(): void
    {
        $this->requireLogin();
        $stmt = $this->db->pdo()->query('SELECT * FROM templates ORDER BY created_at DESC');
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('user/templates/index', [
            'templates' => $templates,
        ]);
    }

    public function renderForm(): void
    {
        $this->requireLogin();
        $stmt = $this->db->pdo()->query('SELECT * FROM templates ORDER BY created_at DESC');
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rendersStmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
        $rendersStmt->execute([$this->auth->user()['id']]);
        $renders = $rendersStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('user/render/index', [
            'templates' => $templates,
            'renders' => $renders,
            'csrf' => $this->csrf,
        ]);
    }

    public function templateDetail(array $params): void
    {
        $this->requireLogin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT t.*, f.id as field_id, f.field_key, f.x, f.y, f.w, f.h, f.padding, f.font_id, f.base_font_size, f.min_font_size, f.max_lines, f.line_height, f.color, f.align, f.valign, f.draw_order FROM templates t LEFT JOIN template_text_fields f ON t.id = f.template_id WHERE t.id = ?');
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            $this->response->json(['error' => 'Not found'], 404);
        }
        $template = null;
        $fields = [];
        foreach ($rows as $row) {
            if (!$template) {
                $template = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'width' => (int)$row['width'],
                    'height' => (int)$row['height'],
                    'overlay_png_path' => $row['overlay_png_path'],
                    'media_fit_mode' => $row['media_fit_mode'],
                    'background_color' => $row['background_color'],
                    'export_format' => $row['export_format'],
                ];
            }
            if ($row['field_id']) {
                $fields[] = [
                    'field_key' => $row['field_key'],
                    'x' => (int)$row['x'],
                    'y' => (int)$row['y'],
                    'w' => (int)$row['w'],
                    'h' => (int)$row['h'],
                    'padding' => (int)$row['padding'],
                    'font_id' => (int)$row['font_id'],
                    'base_font_size' => (int)$row['base_font_size'],
                    'min_font_size' => (int)$row['min_font_size'],
                    'max_lines' => (int)$row['max_lines'],
                    'line_height' => (float)$row['line_height'],
                    'color' => $row['color'],
                    'align' => $row['align'],
                    'valign' => $row['valign'],
                    'draw_order' => (int)$row['draw_order'],
                ];
            }
        }
        $this->response->json(['template' => $template, 'fields' => $fields]);
    }

    public function renderImage(): void
    {
        $this->requireLogin();
        $this->csrf->verify();
        $templateId = (int)($_POST['template_id'] ?? 0);
        $headline = trim($_POST['headline'] ?? '');
        $subhead = trim($_POST['subhead'] ?? '');
        if ($templateId <= 0 || empty($_FILES['photo']['tmp_name'])) {
            $this->session->flash('error', 'Template and photo are required.');
            $this->response->redirect('/render');
        }
        if ($_FILES['photo']['size'] > $this->config['app']['upload_max_size']) {
            $this->session->flash('error', 'Photo file is too large.');
            $this->response->redirect('/render');
        }
        $file = $_FILES['photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->session->flash('error', 'Photo must be JPG, PNG, or WEBP.');
            $this->response->redirect('/render');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $this->session->flash('error', 'Invalid photo mime type.');
            $this->response->redirect('/render');
        }

        $stmt = $this->db->pdo()->prepare('SELECT * FROM templates WHERE id = ?');
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$template) {
            $this->session->flash('error', 'Template not found.');
            $this->response->redirect('/render');
        }
        if (empty($template['overlay_png_path'])) {
            $this->session->flash('error', 'Template is missing overlay PNG.');
            $this->response->redirect('/render');
        }

        $filename = $this->storage->randomName('input', $ext);
        $dest = $this->storage->path('uploads') . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->flash('error', 'Failed to save uploaded photo.');
            $this->response->redirect('/render');
        }

        $fieldsStmt = $this->db->pdo()->prepare('SELECT f.*, fonts.file_path FROM template_text_fields f LEFT JOIN fonts ON fonts.id = f.font_id WHERE f.template_id = ? ORDER BY draw_order');
        $fieldsStmt->execute([$templateId]);
        $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fields as &$field) {
            $field['font_path'] = $field['file_path'] ? $this->storage->path('fonts') . '/' . $field['file_path'] : null;
        }

        $insert = $this->db->pdo()->prepare('INSERT INTO renders (user_id, template_id, input_path, status, params_json, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $params = json_encode(['headline' => $headline, 'subhead' => $subhead], JSON_UNESCAPED_UNICODE);
        $insert->execute([$this->auth->user()['id'], $templateId, $filename, 'processing', $params]);
        $renderId = (int)$this->db->pdo()->lastInsertId();

        $previewName = $this->storage->randomName('preview', 'jpg');
        $outputName = $this->storage->randomName('render', $template['export_format']);
        $previewPath = $this->storage->path('previews') . '/' . $previewName;
        $outputPath = $this->storage->path('renders') . '/' . $outputName;
        $overlayPath = $this->storage->path('overlays') . '/' . $template['overlay_png_path'];

        try {
            $this->renderer->render($template, $fields, $dest, $outputPath, $previewPath, $headline, $subhead, $overlayPath);
            $update = $this->db->pdo()->prepare('UPDATE renders SET output_path = ?, preview_path = ?, status = ? WHERE id = ?');
            $update->execute([$outputName, $previewName, 'done', $renderId]);
            $this->session->flash('success', 'Render completed.');
        } catch (\Throwable $e) {
            $update = $this->db->pdo()->prepare('UPDATE renders SET status = ? WHERE id = ?');
            $update->execute(['failed', $renderId]);
            $this->session->flash('error', 'Render failed: ' . $e->getMessage());
        }

        $this->response->redirect('/render');
    }

    public function renderStatus(array $params): void
    {
        $this->requireLogin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->auth->user()['id']]);
        $render = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$render) {
            $this->response->json(['error' => 'Not found'], 404);
        }
        $render['preview_url'] = $render['preview_path'] ? '/preview/' . $render['preview_path'] : null;
        $render['download_url'] = $render['output_path'] ? '/download/' . $render['id'] : null;
        $this->response->json($render);
    }

    public function preview(array $params): void
    {
        $this->requireLogin();
        $file = basename($params['id']);
        $path = $this->storage->path('previews') . '/' . $file;
        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: image/jpeg');
        if (str_ends_with($file, '.png')) {
            header('Content-Type: image/png');
        }
        readfile($path);
        exit;
    }

    public function download(array $params): void
    {
        $this->requireLogin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM renders WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->auth->user()['id']]);
        $render = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$render || !$render['output_path']) {
            http_response_code(404);
            echo 'Render not found';
            exit;
        }
        $path = $this->storage->path('renders') . '/' . $render['output_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'File missing';
            exit;
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $this->response->file($path, 'render_' . $id . '.' . $ext, $mime);
    }
}

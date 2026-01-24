<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\View;
use App\Services\Renderer;
use App\Services\Storage;
use PDO;

final class AdminTemplatesController
{
    public function __construct(
        private DB $db,
        private Auth $auth,
        private Csrf $csrf,
        private Response $response,
        private View $view,
        private Storage $storage,
        private Renderer $renderer,
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
        $stmt = $this->db->pdo()->query('SELECT * FROM templates ORDER BY created_at DESC');
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('admin/templates/index', [
            'templates' => $templates,
            'csrf' => $this->csrf,
        ]);
    }

    public function createForm(): void
    {
        $this->requireAdmin();
        $this->view->render('admin/templates/create', [
            'csrf' => $this->csrf,
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        $name = trim($_POST['name'] ?? '');
        $width = (int)($_POST['width'] ?? 0);
        $height = (int)($_POST['height'] ?? 0);
        $fit = $_POST['media_fit_mode'] ?? 'cover';
        $background = $_POST['background_color'] ?? '#000000';
        $format = $_POST['export_format'] ?? 'jpg';
        if ($name === '' || $width <= 0 || $height <= 0) {
            http_response_code(400);
            echo 'Invalid template';
            exit;
        }
        $stmt = $this->db->pdo()->prepare('INSERT INTO templates (name, width, height, media_fit_mode, background_color, export_format, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $width, $height, $fit, $background, $format]);
        $this->response->redirect('/admin/templates');
    }

    public function edit(array $params): void
    {
        $this->requireAdmin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT * FROM templates WHERE id = ?');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$template) {
            http_response_code(404);
            echo 'Template not found';
            exit;
        }
        $fieldStmt = $this->db->pdo()->prepare('SELECT * FROM template_text_fields WHERE template_id = ?');
        $fieldStmt->execute([$id]);
        $fields = $fieldStmt->fetchAll(PDO::FETCH_ASSOC);
        $fieldsByKey = [];
        foreach ($fields as $field) {
            $fieldsByKey[$field['field_key']] = $field;
        }
        $fonts = $this->db->pdo()->query("SELECT * FROM fonts WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view->render('admin/templates/edit', [
            'template' => $template,
            'fields' => $fieldsByKey,
            'fonts' => $fonts,
            'csrf' => $this->csrf,
        ]);
    }

    public function uploadOverlay(array $params): void
    {
        $this->requireAdmin();
        $this->csrf->verify();
        if (empty($_FILES['overlay']['tmp_name'])) {
            http_response_code(400);
            echo 'Missing overlay';
            exit;
        }
        if ($_FILES['overlay']['size'] > $this->config['app']['upload_max_size']) {
            http_response_code(400);
            echo 'File too large';
            exit;
        }
        $file = $_FILES['overlay'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'png') {
            http_response_code(400);
            echo 'Overlay must be PNG';
            exit;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime !== 'image/png') {
            http_response_code(400);
            echo 'Invalid overlay mime';
            exit;
        }
        $filename = $this->storage->randomName('overlay', 'png');
        $dest = $this->storage->path('overlays') . '/' . $filename;
        move_uploaded_file($file['tmp_name'], $dest);
        $stmt = $this->db->pdo()->prepare('UPDATE templates SET overlay_png_path = ? WHERE id = ?');
        $stmt->execute([$filename, (int)$params['id']]);
        $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
    }

    public function overlayView(array $params): void
    {
        $this->requireAdmin();
        $id = (int)$params['id'];
        $stmt = $this->db->pdo()->prepare('SELECT overlay_png_path FROM templates WHERE id = ?');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$template || !$template['overlay_png_path']) {
            http_response_code(404);
            exit;
        }
        $path = $this->storage->path('overlays') . '/' . $template['overlay_png_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: image/png');
        readfile($path);
        exit;
    }

    public function saveFields(array $params): void
    {
        $this->requireAdmin();
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || empty($payload['fields'])) {
            $this->response->json(['error' => 'Invalid payload'], 400);
        }
        $templateId = (int)$params['id'];
        $db = $this->db->pdo();
        foreach ($payload['fields'] as $key => $field) {
            $stmt = $db->prepare('SELECT id FROM template_text_fields WHERE template_id = ? AND field_key = ?');
            $stmt->execute([$templateId, $key]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $sql = 'UPDATE template_text_fields SET x=?, y=?, w=?, h=?, padding=?, font_id=?, base_font_size=?, min_font_size=?, max_lines=?, line_height=?, color=?, align=?, valign=?, stroke_enabled=?, stroke_width=?, stroke_color=?, shadow_enabled=?, shadow_x=?, shadow_y=?, shadow_blur=?, shadow_color=?, draw_order=? WHERE template_id=? AND field_key=?';
                $update = $db->prepare($sql);
                $update->execute([
                    (int)$field['x'],
                    (int)$field['y'],
                    (int)$field['w'],
                    (int)$field['h'],
                    (int)($field['padding'] ?? 0),
                    (int)($field['font_id'] ?? 0),
                    (int)$field['base_font_size'],
                    (int)$field['min_font_size'],
                    (int)$field['max_lines'],
                    (float)$field['line_height'],
                    $field['color'],
                    $field['align'],
                    $field['valign'],
                    (int)$field['stroke_enabled'],
                    (int)$field['stroke_width'],
                    $field['stroke_color'],
                    (int)$field['shadow_enabled'],
                    (int)$field['shadow_x'],
                    (int)$field['shadow_y'],
                    (int)$field['shadow_blur'],
                    $field['shadow_color'],
                    (int)($field['draw_order'] ?? 1),
                    $templateId,
                    $key,
                ]);
            } else {
                $sql = 'INSERT INTO template_text_fields (template_id, field_key, x, y, w, h, padding, font_id, base_font_size, min_font_size, max_lines, line_height, color, align, valign, stroke_enabled, stroke_width, stroke_color, shadow_enabled, shadow_x, shadow_y, shadow_blur, shadow_color, draw_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
                $insert = $db->prepare($sql);
                $insert->execute([
                    $templateId,
                    $key,
                    (int)$field['x'],
                    (int)$field['y'],
                    (int)$field['w'],
                    (int)$field['h'],
                    (int)($field['padding'] ?? 0),
                    (int)($field['font_id'] ?? 0),
                    (int)$field['base_font_size'],
                    (int)$field['min_font_size'],
                    (int)$field['max_lines'],
                    (float)$field['line_height'],
                    $field['color'],
                    $field['align'],
                    $field['valign'],
                    (int)$field['stroke_enabled'],
                    (int)$field['stroke_width'],
                    $field['stroke_color'],
                    (int)$field['shadow_enabled'],
                    (int)$field['shadow_x'],
                    (int)$field['shadow_y'],
                    (int)$field['shadow_blur'],
                    $field['shadow_color'],
                    (int)($field['draw_order'] ?? 1),
                ]);
            }
        }
        $this->response->json(['status' => 'ok']);
    }

    public function testRender(array $params): void
    {
        $this->requireAdmin();
        $templateId = (int)$params['id'];
        $templateStmt = $this->db->pdo()->prepare('SELECT * FROM templates WHERE id = ?');
        $templateStmt->execute([$templateId]);
        $template = $templateStmt->fetch(PDO::FETCH_ASSOC);
        if (!$template) {
            $this->response->json(['error' => 'Template not found'], 404);
        }
        $fieldsStmt = $this->db->pdo()->prepare('SELECT f.*, fonts.file_path FROM template_text_fields f LEFT JOIN fonts ON fonts.id = f.font_id WHERE f.template_id = ? ORDER BY draw_order');
        $fieldsStmt->execute([$templateId]);
        $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fields as &$field) {
            $field['font_path'] = $field['file_path'] ? $this->storage->path('fonts') . '/' . $field['file_path'] : null;
        }

        $samplePath = $this->storage->path('uploads') . '/sample.jpg';
        if (!file_exists($samplePath)) {
            $sampleImage = imagecreatetruecolor(1200, 800);
            $bg = imagecolorallocate($sampleImage, 80, 80, 80);
            imagefill($sampleImage, 0, 0, $bg);
            imagejpeg($sampleImage, $samplePath, 85);
            imagedestroy($sampleImage);
        }

        $output = $this->storage->path('renders') . '/' . $this->storage->randomName('render', $template['export_format']);
        $preview = $this->storage->path('previews') . '/' . $this->storage->randomName('preview', 'jpg');
        $overlay = $template['overlay_png_path'] ? $this->storage->path('overlays') . '/' . $template['overlay_png_path'] : null;

        $this->renderer->render($template, $fields, $samplePath, $output, $preview, 'Headline Example', 'Subhead example goes here', $overlay);

        $previewId = basename($preview);
        $this->response->json(['preview_url' => '/preview/' . $previewId]);
    }
}

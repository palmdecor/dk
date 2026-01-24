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
        private ImageRenderer $renderer,
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
            echo 'Yetkisiz';
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $templates = $this->db->pdo()->query('SELECT * FROM templates ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        $this->view->render('admin/templates', [
            'templates' => $templates,
            'csrf' => $this->csrf,
        ]);
    }

    public function createForm(): void
    {
        $this->requireAdmin();
        $this->view->render('admin/template_create', [
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
            $this->session->flash('error', 'Şablon adı ve ölçüler zorunludur.');
            $this->response->redirect('/admin/templates/create');
        }
        $stmt = $this->db->pdo()->prepare('INSERT INTO templates (name, width, height, media_fit_mode, background_color, export_format, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $width, $height, $fit, $background, $format]);
        $this->session->flash('success', 'Şablon oluşturuldu.');
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
            echo 'Şablon bulunamadı';
            exit;
        }

        $this->ensureDefaultFields($id);

        $fieldStmt = $this->db->pdo()->prepare('SELECT * FROM template_text_fields WHERE template_id = ?');
        $fieldStmt->execute([$id]);
        $fields = $fieldStmt->fetchAll(PDO::FETCH_ASSOC);
        $fieldsByKey = [];
        foreach ($fields as $field) {
            $fieldsByKey[$field['field_key']] = $field;
        }
        $fonts = $this->db->pdo()->query("SELECT * FROM fonts WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view->render('admin/template_edit', [
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
            $this->session->flash('error', 'Overlay PNG yükleyin.');
            $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
        }
        if ($_FILES['overlay']['size'] > $this->config['app']['upload_max_size']) {
            $this->session->flash('error', 'Overlay dosyası çok büyük.');
            $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
        }
        $file = $_FILES['overlay'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'png') {
            $this->session->flash('error', 'Overlay PNG olmalıdır.');
            $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime !== 'image/png') {
            $this->session->flash('error', 'Overlay MIME hatası.');
            $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
        }
        $filename = $this->storage->randomName('overlay', 'png');
        $dest = $this->storage->path('overlays') . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->session->flash('error', 'Overlay kaydedilemedi.');
            $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
        }
        $stmt = $this->db->pdo()->prepare('UPDATE templates SET overlay_png_path = ? WHERE id = ?');
        $stmt->execute([$filename, (int)$params['id']]);
        $this->session->flash('success', 'Overlay yüklendi.');
        $this->response->redirect('/admin/templates/' . (int)$params['id'] . '/edit');
    }

    public function saveFields(array $params): void
    {
        $this->requireAdmin();
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || empty($payload['fields'])) {
            $this->response->json(['error' => 'Geçersiz veri'], 400);
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
        $template = $this->db->pdo()->prepare('SELECT * FROM templates WHERE id = ?');
        $template->execute([$templateId]);
        $tpl = $template->fetch(PDO::FETCH_ASSOC);
        if (!$tpl) {
            $this->response->json(['error' => 'Şablon bulunamadı'], 404);
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
            imagejpeg($sampleImage, $samplePath, 95);
            imagedestroy($sampleImage);
        }

        $output = $this->storage->path('renders') . '/' . $this->storage->randomName('render', $tpl['export_format']);
        $preview = $this->storage->path('previews') . '/' . $this->storage->randomName('preview', 'jpg');
        $overlay = $tpl['overlay_png_path'] ? $this->storage->path('overlays') . '/' . $tpl['overlay_png_path'] : null;

        try {
            $this->renderer->render($tpl, $fields, $samplePath, $output, $preview, 'Başlık Örneği', 'Alt başlık örneği', $overlay, [
                'offset_x' => 0,
                'offset_y' => 0,
                'zoom' => 1.0,
            ]);
            $previewId = pathinfo($preview, PATHINFO_FILENAME);
            $this->response->json(['preview_url' => '/download/' . $previewId]);
        } catch (\Throwable $e) {
            $this->response->json(['error' => 'Render başarısız: ' . $e->getMessage()], 500);
        }
    }

    private function ensureDefaultFields(int $templateId): void
    {
        $stmt = $this->db->pdo()->prepare('SELECT field_key FROM template_text_fields WHERE template_id = ?');
        $stmt->execute([$templateId]);
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $defaults = [
            'headline' => [
                'x' => 60,
                'y' => 60,
                'w' => 520,
                'h' => 200,
                'padding' => 0,
                'font_id' => null,
                'base_font_size' => 48,
                'min_font_size' => 20,
                'max_lines' => 3,
                'line_height' => 1.2,
                'color' => '#ffffff',
                'align' => 'left',
                'valign' => 'top',
                'stroke_enabled' => 0,
                'stroke_width' => 2,
                'stroke_color' => '#000000',
                'shadow_enabled' => 0,
                'shadow_x' => 2,
                'shadow_y' => 2,
                'shadow_blur' => 4,
                'shadow_color' => '#000000',
                'draw_order' => 1,
            ],
            'subhead' => [
                'x' => 60,
                'y' => 320,
                'w' => 520,
                'h' => 160,
                'padding' => 0,
                'font_id' => null,
                'base_font_size' => 32,
                'min_font_size' => 16,
                'max_lines' => 3,
                'line_height' => 1.2,
                'color' => '#ffffff',
                'align' => 'left',
                'valign' => 'top',
                'stroke_enabled' => 0,
                'stroke_width' => 2,
                'stroke_color' => '#000000',
                'shadow_enabled' => 0,
                'shadow_x' => 2,
                'shadow_y' => 2,
                'shadow_blur' => 4,
                'shadow_color' => '#000000',
                'draw_order' => 2,
            ],
        ];

        foreach ($defaults as $key => $data) {
            if (in_array($key, $existing, true)) {
                continue;
            }
            $insert = $this->db->pdo()->prepare(
                'INSERT INTO template_text_fields (template_id, field_key, x, y, w, h, padding, font_id, base_font_size, min_font_size, max_lines, line_height, color, align, valign, stroke_enabled, stroke_width, stroke_color, shadow_enabled, shadow_x, shadow_y, shadow_blur, shadow_color, draw_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $insert->execute([
                $templateId,
                $key,
                $data['x'],
                $data['y'],
                $data['w'],
                $data['h'],
                $data['padding'],
                $data['font_id'],
                $data['base_font_size'],
                $data['min_font_size'],
                $data['max_lines'],
                $data['line_height'],
                $data['color'],
                $data['align'],
                $data['valign'],
                $data['stroke_enabled'],
                $data['stroke_width'],
                $data['stroke_color'],
                $data['shadow_enabled'],
                $data['shadow_x'],
                $data['shadow_y'],
                $data['shadow_blur'],
                $data['shadow_color'],
                $data['draw_order'],
            ]);
        }
    }
}

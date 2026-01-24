<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Şablonlar</h1>
<div class="panel">
  <a class="button-link" href="/admin/templates/create">Yeni Şablon Oluştur</a>
  <table class="table">
    <thead>
      <tr>
        <th>Ad</th>
        <th>Boyut</th>
        <th>Overlay</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($templates)): ?>
        <tr><td colspan="4">Şablon yok</td></tr>
      <?php endif; ?>
      <?php foreach ($templates as $template): ?>
        <tr>
          <td><?php echo Helpers::e($template['name']); ?></td>
          <td><?php echo (int)$template['width']; ?> x <?php echo (int)$template['height']; ?></td>
          <td><?php echo $template['overlay_png_path'] ? 'Yüklü' : 'Eksik'; ?></td>
          <td><a class="button-link" href="/admin/templates/<?php echo (int)$template['id']; ?>/edit">Düzenle</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Şablonlar';
include __DIR__ . '/../layout.php';
?>

<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Fontlar</h1>
<?php if (!$freetype): ?>
  <div class="notice notice-warning">GD FreeType desteği yok. Imagick yoksa render sorunlu olabilir.</div>
<?php endif; ?>
<form method="post" action="/admin/fonts/upload" enctype="multipart/form-data">
  <?php echo $csrf->field(); ?>
  <label>Font Adı</label>
  <input name="name" required>
  <label>Font Dosyası (ttf/otf)</label>
  <input type="file" name="font" required>
  <button type="submit">Yükle</button>
</form>
<table class="table">
  <thead>
    <tr>
      <th>Ad</th>
      <th>Durum</th>
      <th>Önizleme</th>
      <th>İşlem</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($fonts)): ?>
      <tr><td colspan="4">Font yok</td></tr>
    <?php endif; ?>
    <?php foreach ($fonts as $font): ?>
      <?php $previewFile = $previewPath . '/font_' . (int)$font['id'] . '.png'; ?>
      <tr>
        <td><?php echo Helpers::e($font['name']); ?></td>
        <td><?php echo Helpers::e($font['status']); ?></td>
        <td>
          <?php if (file_exists($previewFile)): ?>
            <img src="/download/<?php echo Helpers::e('font_' . (int)$font['id']); ?>" alt="Font preview" width="160">
          <?php else: ?>
            <span class="small">Önizleme yok</span>
          <?php endif; ?>
        </td>
        <td>
          <form method="post" action="/admin/fonts/toggle/<?php echo (int)$font['id']; ?>">
            <?php echo $csrf->field(); ?>
            <button class="secondary" type="submit">Aktif/Pasif</button>
          </form>
          <form method="post" action="/admin/fonts/test/<?php echo (int)$font['id']; ?>">
            <?php echo $csrf->field(); ?>
            <button type="submit">Test</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Fontlar';
include __DIR__ . '/../layout.php';
?>

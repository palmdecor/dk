<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Fonts</h1>
<?php if (!$freetype): ?>
  <div class="notice notice-warning">GD FreeType support is not available. Font rendering may fail without Imagick.</div>
<?php endif; ?>
<form method="post" action="/admin/fonts/upload" enctype="multipart/form-data">
  <?php echo $csrf->field(); ?>
  <label>Font name</label>
  <input name="name" required>
  <label>Font file (ttf/otf)</label>
  <input type="file" name="font" required>
  <button type="submit">Upload</button>
</form>
<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Status</th>
      <th>Preview</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($fonts)): ?>
      <tr><td colspan="4">No fonts</td></tr>
    <?php endif; ?>
    <?php foreach ($fonts as $font): ?>
      <?php $previewFile = $previewPath . '/font_' . (int)$font['id'] . '.png'; ?>
      <tr>
        <td><?php echo Helpers::e($font['name']); ?></td>
        <td><?php echo Helpers::e($font['status']); ?></td>
        <td>
          <?php if (file_exists($previewFile)): ?>
            <img src="/preview/<?php echo Helpers::e('font_' . (int)$font['id'] . '.png'); ?>" alt="Font preview" width="160">
          <?php else: ?>
            <span class="small">No preview yet</span>
          <?php endif; ?>
        </td>
        <td>
          <form method="post" action="/admin/fonts/<?php echo (int)$font['id']; ?>/toggle">
            <?php echo $csrf->field(); ?>
            <button type="submit">Toggle</button>
          </form>
          <a class="button-link" href="/admin/fonts/test/<?php echo (int)$font['id']; ?>">Font Test</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Fonts';
include __DIR__ . '/../../layout.php';
?>

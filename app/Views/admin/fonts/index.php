<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Fonts</h1>
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
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($fonts)): ?>
      <tr><td colspan="3">No fonts</td></tr>
    <?php endif; ?>
    <?php foreach ($fonts as $font): ?>
      <tr>
        <td><?php echo Helpers::e($font['name']); ?></td>
        <td><?php echo Helpers::e($font['status']); ?></td>
        <td>
          <form method="post" action="/admin/fonts/<?php echo (int)$font['id']; ?>/toggle">
            <?php echo $csrf->field(); ?>
            <button type="submit">Toggle</button>
          </form>
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

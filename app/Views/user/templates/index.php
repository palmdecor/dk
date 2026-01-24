<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Templates</h1>
<p class="small">Browse available templates. Use the Render page to create output.</p>
<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Size</th>
      <th>Fit</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($templates)): ?>
      <tr><td colspan="3">No templates available.</td></tr>
    <?php endif; ?>
    <?php foreach ($templates as $template): ?>
      <tr>
        <td><?php echo Helpers::e($template['name']); ?></td>
        <td><?php echo (int)$template['width']; ?> x <?php echo (int)$template['height']; ?></td>
        <td><?php echo Helpers::e($template['media_fit_mode']); ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Templates';
include __DIR__ . '/../../layout.php';
?>

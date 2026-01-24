<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Templates</h1>
<p class="notice">Create templates, upload overlays, and open the editor to define headline/subhead fields.</p>
<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Size</th>
      <th>Overlay</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($templates)): ?>
      <tr><td colspan="4">No templates yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($templates as $template): ?>
      <tr>
        <td><?php echo Helpers::e($template['name']); ?></td>
        <td><?php echo (int)$template['width']; ?> x <?php echo (int)$template['height']; ?></td>
        <td><?php echo $template['overlay_png_path'] ? 'Uploaded' : 'Missing'; ?></td>
        <td>
          <a href="/admin/templates/<?php echo (int)$template['id']; ?>/edit">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Templates';
include __DIR__ . '/../../layout.php';
?>
